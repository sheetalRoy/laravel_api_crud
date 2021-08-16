<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Result;
use Illuminate\Support\Facades\Auth;
use Validator;

class ResultController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(), [ 
            'key' => 'required', 
            'value' => 'required',
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }
        $input = $request->all();
        $input['value'] = json_encode($input['value']);
    		$insert = Result::create($input);
	        $success['success'] =  'Record added';	
       
        return response()->json(['success'=>$success], 200); 
    }

    public function updateResult($key, Request $request){
        $input = $request->all();
        $input['value'] = json_encode($input['value']);
        $obj = Result::where('key','=',$key)->first();
        $obj->update(['value' => $input['value']]);
        $success['success'] =  'Record updated';
            
        return response()->json(['success'=>$success], 200);    
    }

    public function getResult(){
        $results = Result::all();
        return [
            'data' => $results,
            'status' => 200
        ];
    }
    public function result($id){
        // $obj = Result::where('key','=',$key)->first();
        $results = Result::find($id);
        $key = $results->key;
        $value = json_decode($results->value);
        return [
            'key' => $key,
            'value' => $value
        ];
    }
}
