<?php
/****************************************************************************
				/ingredient/add
//////////////////////////////////////////////////////////////////////////////

function reads post request on /ingredient/add path 
then it looks for parameters we need
if we dont find them then it throws error
if we do then it imports database file and creates connection in initialize()
if connection succeeds then it imports query initialize()

*****************************************************************************************
it checks if username exists by checking if user id for that user exists , 
if user_id is returned then add the ingredient to ingredient table
then extract id of that ingredient from ingredient table
add user_id and ingredient_if to the user-ingredient table

*****************************************************************************
input: POST "user","count" required on /ingredient/return
output: Content-Type', 'application/json file
		if succeeds then returns json string HTTP status 200
			{"result": "succeed", "ingredients":list of ingredient names}
		is fails then HTTP status 403
			{"Error":{the list of error}, "Result": "failed"}
///////////////////////////////////////////////////////////////////////////////
******************************************************************************/
$app->post('/ingredient/add', function($request, $response, $path = null) {
	$data = $request->getParsedBody();

	// create error array
	$error=[];

	//check if exists
	if(!isset($data['user'])){$error["Error"]["username"]="not entered";}
	if(!isset($data['ingredient'])){$error["Error"]["ingredient"]="not entered";}
	
	// if no error continue
	if(sizeof($error)==0){

		// init first
        initialize();
		$db=db_connect($error);

		if($db){
			// sanitize
			$user= filter_var($data['user'],FILTER_SANITIZE_STRING);
			$ingredient = filter_var($data['ingredient'],FILTER_SANITIZE_STRING);
			
			// add user
			add_ingredient($user,$ingredient,$db,$error);
			
			// if no error respond
			if(sizeof($error)==0){
				$out= json_encode(["Result" => "succeed"]);
				$response->getBody()->write($out);
			}
			
			// close connection
			db_close($db);
		}
	}
	// check error
	if (sizeof($error)>0){
		$error["Result"]="failed";
		$out= json_encode($error,JSON_FORCE_OBJECT);
		$response->getBody()->write($out);
		$response= $response->withStatus(403);
	}

	$response = $response->withHeader('Content-Type', 'application/json');
    return $response;
});