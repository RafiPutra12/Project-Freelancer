<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Services;
use App\User;
use DB;
use App\Label;
use App\Labelrelations;

class ServicesController extends Controller
{
	public function tambah(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'userid'       => 'required|integer',
			'name'         => 'required|string|max:255',
			'descriptions' => 'required|string|max:255',
			'minimumprice' => 'required|integer',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status'	=> 0,
				'message'	=> $validator->errors()->toJson()
			]);
		}

		$dt_service = array(
			'userid'                => $request->input('userid'),
			'name'          		=> $request->input('name'),
			'descriptions'          => $request->input('descriptions'),
			'minimumprice'          => $request->input('minimumprice'),
			'created_at'            => date('Y-m-d H:i:s'),
			'updated_at'            => date('Y-m-d H:i:s'),
		);
		$last_insert_service = DB::table('services')->insertGetId($dt_service);

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
			$label_relation = array('labelid' => $label_selected_id, 'relid' => $last_insert_service, 'type' => "service");
			$label_selected_id = DB::table('labelrelations')->insert($label_relation);
		}

		return response()->json([
			'status'  =>  '1',
			'message' =>  'Tambah Data Jasa Berhasil'
		]);
	}
	public function getAll($limit = 10, $offset = 0)
	{
		$data["count"] = Services::count();
		$services      = array();

		foreach (Services::take($limit)->skip($offset)->get() as $p) {
			$item = [
				"id"           => $p->id,
				"name"         => $p->name,
				"descriptions" => $p->descriptions,
				"minimumprice" => $p->minimumprice,
				"status"       => $p->status,
				"created_at"        => date('d M Y',strtotime($p->created_at)),
                "updated_at"        => date('d M Y',strtotime($p->updated_at)),
			];

			array_push($services, $item);
		}
		$data["services"] = $services;
		$data["status"]   = 1;
		return response($data);
	}

	public function show($id)
	{
		$data = Services::where('id', $id)->get();

		$dt_label = array();
		$label = DB::table('labels')->join('labelrelations', 'labelrelations.labelid', '=', 'labels.id')
			->where('labelrelations.relid', $id)
			->where('labelrelations.type', 'service')
			->get();
		foreach ($label as $lbl) {
			array_push($dt_label, $lbl->name);
		}
		$serviceshow = array();
		foreach ($data as $p) {
			$item = [
				"id"           => $p->id,
				"name"         => $p->name,
				"descriptions" => $p->descriptions,
				"minimumprice" => $p->minimumprice,
				"status"       => $p->status,
				"label"             => $dt_label,
				"created_at"        => date('d M Y',strtotime($p->created_at)),
                "updated_at"        => date('d M Y',strtotime($p->updated_at)),

			];
			array_push($serviceshow, $item);
		}
		if (Services::where('id', $id)->count() > 0) {
            return response()->json([
                'status' => '1',
                'data' => $item,
            ]);
        } else {
            return response()->json([
                'status' => '0',
                'message' => 'Data tidak dapat ditemukan'
            ]);
        }

		$dt["service"] = $serviceshow;
		$dt["status"] = 1;
		return response($dt);
	}

	public function update($id, Request $request)
	{
		$validator = Validator::make($request->all(), [
			'name'         => 'required|string|max:255',
			'descriptions' => 'required|string|max:255',
			'minimumprice' => 'required|integer',
		]);

		if ($validator->fails()) {
			return response()->json([
				'status'	=> 0,
				'message'	=> $validator->errors()->toJson()
			]);
		}

		$dt_services = Services::where('id', $id)->first();
	    $dt_services = array(
            'userid'                => $request->input('userid'),
            'name'                  => $request->input('name'),
            'descriptions'          => $request->input('descriptions'),
            'minimumprice'          => $request->input('minimumprice'),
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        );
         DB::table('services')->where('id', $id)->update($dt_services);

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

		return response()->json([
			'status'  =>  '1',
			'message' =>  'Update Data Jasa Berhasil'
		]);
	}

	public function destroy($id)
	{
		$services = Services::where('id', $id)->first();

		if (Services::where('id', $id)->count() > 0) {

			$services->delete();
			return response()->json([
				'status' => '1',
				'data' => 'Hapus Data Jasa Berhasil',
			]);
		} else {
			return response()->json([
				'status' => '0',
				'message' =>  'Hapus Data Jasa Gagal',
			]);
		}
	}
}
