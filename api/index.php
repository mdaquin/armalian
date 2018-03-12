<?php

require_once('inc/functions.php');

if (strcmp($_SERVER['REQUEST_METHOD'], 'GET')==0){
   // text ID given 
   if (isset($_GET['id'])){
       $res = textInfo($_GET['id']);
       echo json_encode($res);
   } 
   // or just give a list
   else {
       $res = new StdClass();
       $res->texts = listTexts();       
       echo json_encode($res);
   }  
} else if (strcmp($_SERVER['REQUEST_METHOD'], 'POST')==0){
  // new annotation 
  if (isset($_POST['na']) && isset($_POST['id']) && isset($_POST['type'])){
     $r = addAnnotation($_POST['id'], $_POST['na'], $_POST['type']);
      if ($r===true){
	  echo '{"status":"success", "message": "annotation created"}';
      } else {
	  echo '{"status":"failed", "message": "Error creating annotation - maybe an unknown type of annotation?"}';
      }
  } 
  // text info
  else if (isset($_POST['title']) && isset($_POST["author"]) && isset($_POST['text'])) {
      if (isset($_POST['id'])){
      // cannot update text
      	 updateText($_POST['id'], $_POST['title'], $_POST['author']);
      } else {
      	 $id = createText($_POST['title'], $_POST['author'], $_POST['text']);
	 echo '{"status":"success", "message": "text created", "id": "'.$id.'"}';
      }
  } else {
     http_response_code(406);
      echo "wrong parameters";
  }
} else {
   http_response_code(406);
   echo "unsupported method";
}

?>
