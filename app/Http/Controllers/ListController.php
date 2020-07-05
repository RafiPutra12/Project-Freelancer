<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\List_Project;

class ListController extends Controller
{
    public function getAll($limit = 10, $offset = 0) {
        $data["count"] = List_Project::count();
        $projects = array();
        foreach (List_Project::take($limit)->skip($offset)->get() as $p) {
            $item = [
                "id"           => $p->id,
                "userid"       => $p->userid,
                "descriptions" => $p->descriptions,
                "budget"       => $p->budget,
                "type"         => $p->type,
                "projectname"  => $p->projectname,
                "status"       => $p->status,
                "created_at"   => $p->created_at,
                "updated_at"   => $p->updated_at,
            ];
            array_push($projects, $item);
        }
        $data["data"] = $projects;
        $data["status"] = 1;
        return response($data);
    }
    public function show($id)
    {
        $projects = List_Project::where('id', $id)->first();
        $data["data"] = $projects;
        $data["status"] = 1;
        return response($data);
    }   
}
