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

	/* Register */
    public function register(Request $request)
    {
        $email = $request->get('email');
        // $validated = $request->validate([
        //     'email' => 'required',
        // ]);
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);
        if($validator->fails()){
            return $validator->errors();
        }else{
            $hashedMail = User::hashMail($email);
            if (User::where('email', $hashedMail)->exists()) {
                return[
                    'message' => 'email_in_use',
                    'status'=>201
                ];
            }
            $user = new User();
            $user->email = $hashedMail;
            $user->results = NULL;
            do {
                $code = strtoupper(substr(md5(time()), 0, 8));
            } while (User::where('code', $code)->exists());

            $user->code = $code;
            $user->save();
            Mail::to($email)->send(new RegisterMail($code));
            if($user){
                    return [
                    'user' => $user,
                    'status' => 200,
                    'message' => 'Register successfully'
                ];
            }else{
                return [
                    'status' => 400,
                    'message' => 'Something went wrong!'
                ];
            }
        }
        
    }
	public function updateResults(ResultsUpdateRequest $request)
    {
        if(Session::has('user')){
            $code = Session::get('user')->code;
            $user = User::where('code', $code)->first();
            $results = [];
            if($user->results && $user->results!='null'){
                $previous_result = json_decode($user->results);
                foreach ($previous_result as $key => $data) {
                    if($key!=date('d/m/Y')){
                        $results[$key] = $data;
                    }
                }
            }
            $results[date('d/m/Y')] = $request->input('results');
            $user->results = json_encode($results);
            $user->save();
            return response()->json([], 200);
        }else{
            session(['guestResult' => $request->input('results')]);
            return response()->json([], 200); 
        }
    }
	 public function favinsert(Request $request) 
    { 
        $validator = Validator::make($request->all(), [ 
            'key' => 'required', 
            'value' => 'required',
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }
        $input = $request->all();

        $existed = Favcontent::where('key','=',$input['key'])->first();
        
        
    	if ($existed) {

            $existed_array = Favcontent::where('key','=',$input['key'])->get()->toArray();
            $existed_array_value = $existed_array[0]['value'];
            $oldValue = json_decode($existed_array_value, true);
            $inputData = $input['value'][0];
            $nid = $inputData['nid'];
            $fev = $inputData['favorite'];
            $nids = array_column($oldValue, 'nid');
            if (in_array($nid, $nids, true)) {
                $key = array_search($nid, $nids);
                $oldValue[$key]['favorite']= $fev;
                $newupdated = json_encode($oldValue);
                $up = Favcontent::where('key','=',$input['key'])->update(['value' => $newupdated]);
                $success['success'] =  'Record updated';
            } else {  
                $oldValue[] = array('nid'=> $nid, 'favorite'=> $fev);
                $newupdated = json_encode($oldValue);
                $up = Favcontent::where('key','=',$input['key'])->update(['value' => $newupdated]);
                $success['success'] =  'Record updated';
            }
    	} else {
    		$input['value'] = json_encode($input['value']);
    		$insert = Favcontent::create($input);
	        $success['success'] =  'Record added';	
    	}
        return response()->json(['success'=>$success], 200); 
    }

    function favget($key='') {
	$fav = Favcontent::where('key','=',$key)->select('key','value')->first();
	if (isset($fav->key)) {
		$success['success'] =  'Response data';
		$success['data'] =  ["key"=>$fav->key, "value"=> json_decode($fav->value,true)];
	}
	else {
    		$success['success'] =  'No Response data';
    		$success['data'] =  [];
	}
	return response()->json(['success'=>$success], 200);
    }
}