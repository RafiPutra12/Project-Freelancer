<?php

namespace App\Http\Controllers;

use App\User;
use DB;
use App\Project;
use App\Label;
use App\Labelrelations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Offers;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid'       => 'required|integer',
            'descriptions' => 'required|string|max:255',
            'budget'       => 'required|integer',
            'type'         => 'required|in:private,public',
            'projectname'  => 'required|string|max:255',
            'status'       => 'required|in:open,close',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'    => $validator->errors()->toJson()
            ]);
        }

        $dt_project = array(
            'userid'                => $request->input('userid'),
            'descriptions'          => $request->input('descriptions'),
            'budget'                => $request->input('budget'),
            'type'                  => $request->input('type'),
            'status'                => $request->input('status'),
            'projectname'           => $request->input('projectname'),
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        );
        $last_insert_project = DB::table('projects')->insertGetId($dt_project);

        if ($request->input('selectLabel') != NULL) {
            //explode data label
            $dt_label = explode(",", rtrim($request->input('selectLabel'), ","));

            for ($x = 0; $x < count($dt_label); $x++) {
                //cek apakah label sudah ada di tabel label, biar tdk duplicate
                $label_selected_id = "";
                $cek_label = Label::where('name', $dt_label[$x])->orderby('id', 'desc');

                //jika belum ada akan insert
                if ($cek_label->count() == 0) {
                    $new_label = array(
                        'name'              => $dt_label[$x],
                        'created_at'        => date('Y-m-d H:i:s'),
                        'updated_at'        => date('Y-m-d H:i:s'),
                    );
                    $label_selected_id = DB::table('labels')->insertGetId($new_label);
                } else {
                    $id_labels = $cek_label->first();
                    $label_selected_id = $id_labels->id;
                }

                //inser ke label relations
                $label_relation = array('labelid' => $label_selected_id, 'relid' => $last_insert_project, 'type' => "project");
                $label_selected_id = DB::table('labelrelations')->insert($label_relation);
            }
        }

        return response()->json([
            'status'  =>  '1',
            'message' =>  'Tambah Data Project Berhasil'
        ]);
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'descriptions' => 'required|string|max:255',
            'budget'       => 'required|integer',
            'type'         => 'required|in:private,public',
            'projectname'  => 'required|string|max:255',
            'status'       => 'required|in:open,close',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 0,
                'message'    => $validator->errors()->toJson()
            ]);
        }
        $dt_project = Project::where('id', $id)->first();
        $dt_project = array(
            'userid'                => $request->input('userid'),
            'descriptions'          => $request->input('descriptions'),
            'budget'                => $request->input('budget'),
            'type'                  => $request->input('type'),
            'status'                => $request->input('status'),
            'projectname'           => $request->input('projectname'),
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        );
         DB::table('projects')->where('id', $id)->update($dt_project);

        //explode data label
        $dt_label = explode(",", rtrim($request->input('selectLabel'), ","));
        //del label relation
        DB::table('labelrelations')->where('relid', $id)->delete();

        for ($x = 0; $x < count($dt_label); $x++) {
            //cek apakah label sudah ada di tabel label, biar tdk duplicate
            $label_selected_id = "";
            $cek_label = Label::where('name', $dt_label[$x])->orderby('id', 'desc');

            //jika belum ada akan insert
            if ($cek_label->count() == 0) {
                $new_label = array(
                    'name'              => $dt_label[$x],
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s')
                );
                $label_selected_id = DB::table('labels')->insertGetId($new_label);
            } else {
                $id_labels = $cek_label->first();
                $label_selected_id = $id_labels->id;
            }

            $label_relation = array('labelid' => $label_selected_id, 'relid' => $id);
            $label_selected_id = DB::table('labelrelations')->insert($label_relation);
        }

        if (Project::where('id', $id)->count() > 0) {
            return response()->json([
                'status' => '1',
                'data' => $dt_project,
            ]);
        } else {
            return response()->json([
                'status' => '0',
                'message' => 'Data tidak dapat ditemukan'
            ]);
        }
        $dt["project"] = $dt_project;
        $dt["status"] = 1;
        return response($dt);
    }

    public function getAll($limit = 10, $offset = 0)
    {
        $data["count"] = Project::count();
        $project = array();

        foreach (Project::take($limit)->skip($offset)->get() as $p) {
            $total_penawaran = Offers::where('relid',$p->id)->count();
            $item = [
                "id"                => $p->id,
                "userid"            => $p->userid,
                "descriptions"      => $p->descriptions,
                "budget"            => $p->budget,
                "type"              => $p->type,
                "projectname"       => $p->projectname,
                "status"            => $p->status,
                "total_penawaran"   =>$total_penawaran,
                "created_at"        => date('d M Y',strtotime($p->created_at)),
                "updated_at"        => date('d M Y',strtotime($p->updated_at)),
            ];

            array_push($project, $item);
        }
        $data["projects"] = $project;
        $data["status"] = 1;
        return response($data);
    }    
    
    public function show($id)
    {
        $data = Project::where('id', $id)->get();

        $dt_label = array();
        $label = DB::table('labels')->join('labelrelations', 'labelrelations.labelid', '=', 'labels.id')
            ->where('labelrelations.relid', $id)
            ->where('labelrelations.type', 'project')
            ->get();
        foreach ($label as $lbl) {
            array_push($dt_label, $lbl->name);
        }

        $projectshow = array();
        foreach ($data as $p) {
            $item = [
                "id"                => $p->id,
                "userid"            => $p->userid,
                "descriptions"      => $p->descriptions,
                "budget"            => $p->budget,
                "type"              => $p->type,
                "projectname"       => $p->projectname,
                "status"            => $p->status,
                "label"             => $dt_label,
                "created_at"        => date('d M Y',strtotime($p->created_at)),
                "updated_at"        => date('d M Y',strtotime($p->updated_at)),
            ];
            array_push($projectshow, $item);
        }
        if (Project::where('id', $id)->count() > 0) {
            return response()->json([
                'status' => '1',
                'data' => $item,
            ]);
        } else {
            return response()->json([
                'status' => '0',
                'message' => 'Data Tidak Dapat Ditemukan'
            ]);
        }


        $dt["project"] = $projectshow;
        $dt["status"] = 1;
        return response($dt);
    }

    public function destroy($id)
	{
		$data = Project::where('id', $id)->first();

		if (Project::where('id', $id)->count() > 0) {

			$data->delete();
			return response()->json([
				'status' => '1',
				'data' => 'Hapus Data Project Berhasil',
			]);
		} else {
			return response()->json([
				'status' => '0',
				'message' =>  'Hapus Data Project Gagal',
			]);
		}
	}
    public function getSearchResults(Request $request)
    {
        $data = $request->get('data');
        $search_drivers = Project::where('userid', 'like', "%{$data}%")
            ->orWhere('descriptions', 'like', "%{$data}%")
            ->orWhere('budget', 'like', "%{$data}%")
            ->orWhere('type', 'like', "%{$data}%")
            ->orWhere('projectname', 'like', "%{$data}%")
            ->orWhere('status', 'like', "%{$data}%")
            ->limit($request->get('limit'))
            ->offset($request->get('offset'));

        $count = $search_drivers->count();
        if ($count > 0) {
            return response()->json([
                'status'    => '1',
                'count'     => $count,
                'data'      => $search_drivers->get()
            ]);
        } else {
            return response()->json([
                'status'    => '0',
                'count'     => $count,
                'message'    => 'Data Tidak Ditemukan'
            ]);
        }
    }
}
