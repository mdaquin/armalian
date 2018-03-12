<?php

function textInfo($id){
    $result = new StdClass();
    $endpoint = 'http://localhost:3030/armalian/sparql';
    $uri = 'http://afel.insight-centre.org/armalian/doc/'.$id;
    $query = <<<QUERY
    select ?id ?title ?author ?text where {
	graph ?g {
	    <$uri> <http://afel.insight-centre.org/armalian/ID> ?id .
	    <$uri> <http://afel.insight-centre.org/armalian/title> ?title .
	    <$uri> <http://afel.insight-centre.org/armalian/author> ?author .	    
	    <$uri> <http://afel.insight-centre.org/armalian/text> ?text
	}
    }
QUERY;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint.'?query='.urlencode($query).'&output=json');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $data = json_decode($res);
    $list = new StdClass();
    foreach($data->results->bindings as $item){
	$result->id = $item->id->value;
	$result->title = $item->title->value;
	$result->author = $item->author->value;
	$result->text = base64_decode($item->text->value);
    }
    $query = <<<QUERY
    select ?earg ?dp ?arg ?text where {
	graph ?g {
		?dp <http://afel.insight-centre.org/armalian/partOf> <$uri> .	    
		?dp <http://afel.insight-centre.org/armalian/includes> ?earg .	    
		?dp <http://afel.insight-centre.org/armalian/text> ?text .	    
	        ?earg <http://afel.insight-centre.org/armalian/expresses> ?a .
	        ?a a <http://afel.insight-centre.org/armalian/Argument> .
		?a <http://afel.insight-centre.org/armalian/label> ?arg .	    	    
	}
    }
QUERY;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint.'?query='.urlencode($query).'&output=json');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $data = json_decode($res);
    $args = array();
    foreach($data->results->bindings as $item){
	$arg = new StdClass();
	$arg->argument = $item->arg->value;
	$arg->selection = new StdClass();
	$arg->selection->text = base64_decode($item->text->value);
	$arg->docpoart = $item->dp->value;
	$arg->expression = $item->earg->value;
	$args[] = $arg;
    }
    $result->arguments = $args;

    $query = <<<QUERY
    select ?eprop ?dp ?prop ?text where {
	graph ?g {
		?dp <http://afel.insight-centre.org/armalian/partOf> <$uri> .	    
		?dp <http://afel.insight-centre.org/armalian/includes> ?eprop .	    	    
		?dp <http://afel.insight-centre.org/armalian/text> ?text .	    
	        ?eprop <http://afel.insight-centre.org/armalian/expresses> ?a .
	        ?a a <http://afel.insight-centre.org/armalian/Proposition> .
		?a <http://afel.insight-centre.org/armalian/label> ?prop .	    	    
	}
    }
QUERY;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint.'?query='.urlencode($query).'&output=json');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $data = json_decode($res);
    $props = array();
    foreach($data->results->bindings as $item){
	$prop = new StdClass();
	$prop->proposition = $item->prop->value;
	$prop->selection = new StdClass();
	$prop->selection->text = base64_decode($item->text->value);
	$prop->docpoart = $item->dp->value;
	$prop->expression = $item->eprop->value;
	$props[] = $prop;
    }
    $result->propositions = $props;

    $query = <<<QUERY
    select ?dp ?mop ?text where {
	graph ?g {
		?dp <http://afel.insight-centre.org/armalian/partOf> <$uri> .	    
		?dp <http://afel.insight-centre.org/armalian/introduces> ?a .	    	    
		?dp <http://afel.insight-centre.org/armalian/text> ?text .	    
		?a <http://afel.insight-centre.org/armalian/label> ?mop .	    	    
	}
    }
QUERY;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint.'?query='.urlencode($query).'&output=json');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $data = json_decode($res);
    $mops = array();
    foreach($data->results->bindings as $item){
	$mop = new StdClass();
	$mop->mop = $item->mop->value;
	$mop->selection = new StdClass();
	$mop->selection->text = base64_decode($item->text->value);
	$mops[] = $mop;
    }
    $result->mops = $mops;
    return $result;
}

function listTexts(){
    $endpoint = 'http://localhost:3030/armalian/sparql';
    $query = <<<QUERY
    select ?id ?title where {
	graph <http://afel.insight-centre.org/armalian/tool> {
	    ?t a <http://afel.insight-centre.org/armalian/Text> .
	    ?t <http://afel.insight-centre.org/armalian/title> ?title .
	    ?t <http://afel.insight-centre.org/armalian/ID> ?id 
	}
    }
QUERY;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint.'?query='.urlencode($query).'&output=json');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $data = json_decode($res);
    $list = new StdClass();
    foreach($data->results->bindings as $item){
	$list->{$item->id->value} = $item->title->value;
    }
    return $list;
}

function listAuthors(){
    echo "not implemented 2";
}

function listArguments(){
    echo "not implemented 3";
}

function listStatements(){
    echo "not implemented 4";
}

function addAnnotation($id, $na, $type){
    if (strcmp($type, "argument") == 0){
	return addArgument($id, $na);
    } else if (strcmp($type, "proposition") == 0){
	return addProposition($id, $na);    
    } else if (strcmp($type, "mop") == 0){
	return addMOP($id, $na);
    }
    else {
	return false;
    }
}

function addArgument($id, $na){
    $uendpoint = 'http://localhost:3030/armalian/update';
    $duri = 'http://afel.insight-centre.org/armalian/doc/'.$id;
    $auri = 'http://afel.insight-centre.org/armalian/arg/'.md5($na["argument"]);
    $eauri = 'http://afel.insight-centre.org/armalian/exparg/'.$id.'/'.md5($na["selection"]["text"]).'/'.md5($na["argument"]);
    $dpuri = 'http://afel.insight-centre.org/armalian/dp/'.$id.'/'.md5($na["selection"]["text"]);
    $suri = 'http://afel.insight-centre.org/armalian/subject/'.md5($na["subject"]);
    $afuri = 'http://afel.insight-centre.org/armalian/argform/'.md5($na["argumform"]);    
    $text = base64_encode($na["selection"]["text"]);
    $arg = $na["argument"];
    $desc = $na["descriptiom"];
    $sub = $na["subject"];
    $af = $na["argumform"];
    $query = <<<QUERY
insert {
    graph <http://afel.insight-centre.org/armalian/tool> {
	<$auri> a <http://afel.insight-centre.org/armalian/Argument> .
	<$auri> <http://afel.insight-centre.org/armalian/label> "$arg".
	<$auri> <http://afel.insight-centre.org/armalian/description> "$desc".
	<$auri> <http://afel.insight-centre.org/armalian/subject> <$suri>.    
	<$suri> <http://afel.insight-centre.org/armalian/label> "$sub".        
	<$eauri> a <http://afel.insight-centre.org/armalian/ExpressionOfAnArgument> . 
        <$eauri> <http://afel.insight-centre.org/armalian/expresses> <$auri> .        
        <$eauri> <http://afel.insight-centre.org/armalian/inFormOf> <$afuri> .        
	<$afuri> <http://afel.insight-centre.org/armalian/label> "$af".        
	<$dpuri> a <http://afel.insight-centre.org/armalian/DocumentPart> .
	<$dpuri> <http://afel.insight-centre.org/armalian/partOf> <$duri> .
	<$dpuri> <http://afel.insight-centre.org/armalian/text> "$text" .
	<$dpuri> <http://afel.insight-centre.org/armalian/includes> <$eauri> .    
    }
}
where {}
QUERY;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uendpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("update" => $query)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    return true;
}


function addProposition($id, $na){
    $uendpoint = 'http://localhost:3030/armalian/update';
    $duri = 'http://afel.insight-centre.org/armalian/doc/'.$id;
    $puri = 'http://afel.insight-centre.org/armalian/prop/'.md5($na["proposition"]);
    $epuri = 'http://afel.insight-centre.org/armalian/expprop/'.$id.'/'.md5($na["selection"]["text"]).'/'.md5($na["proposition"]);
    $dpuri = 'http://afel.insight-centre.org/armalian/dp/'.$id.'/'.md5($na["selection"]["text"]);
    $suri = 'http://afel.insight-centre.org/armalian/subject/'.md5($na["subject"]);
    $text = base64_encode($na["selection"]["text"]);
    $prop = $na["proposition"];
    $desc = $na["descriptiom"];
    $sub = $na["subject"];
    $query = <<<QUERY
insert {
    graph <http://afel.insight-centre.org/armalian/tool> {
	<$puri> a <http://afel.insight-centre.org/armalian/Proposition> .
	<$puri> <http://afel.insight-centre.org/armalian/label> "$prop".
	<$puri> <http://afel.insight-centre.org/armalian/description> "$desc".
	<$puri> <http://afel.insight-centre.org/armalian/subject> <$suri>.    
	<$suri> <http://afel.insight-centre.org/armalian/label> "$sub".        
	<$epuri> a <http://afel.insight-centre.org/armalian/ExpressionOfAProposition> . 
        <$epuri> <http://afel.insight-centre.org/armalian/expresses> <$puri> .        
	<$dpuri> a <http://afel.insight-centre.org/armalian/DocumentPart> .
	<$dpuri> <http://afel.insight-centre.org/armalian/partOf> <$duri> .
	<$dpuri> <http://afel.insight-centre.org/armalian/text> "$text" .
	<$dpuri> <http://afel.insight-centre.org/armalian/includes> <$epuri> .    
    }
}
where {}
QUERY;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uendpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("update" => $query)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    return true;
}


function addMOP($id, $na){
    $uendpoint = 'http://localhost:3030/armalian/update';
    $duri = 'http://afel.insight-centre.org/armalian/doc/'.$id;
    $muri = 'http://afel.insight-centre.org/armalian/mop/'.md5($na["mop"]);
    $dpuri = 'http://afel.insight-centre.org/armalian/dp/'.$id.'/'.md5($na["selection"]["text"]);
    $text = base64_encode($na["selection"]["text"]);
    $mop = $na["mop"];
    $query = <<<QUERY
insert {
    graph <http://afel.insight-centre.org/armalian/tool> {
	<$muri> a <http://afel.insight-centre.org/armalian/ModeOfPersuasion> .
	<$muri> <http://afel.insight-centre.org/armalian/label> "$mop".
	<$dpuri> a <http://afel.insight-centre.org/armalian/DocumentPart> .
	<$dpuri> <http://afel.insight-centre.org/armalian/partOf> <$duri> .
	<$dpuri> <http://afel.insight-centre.org/armalian/text> "$text" .
	<$dpuri> <http://afel.insight-centre.org/armalian/introduces> <$muri> .    
    }
}
where {}
QUERY;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uendpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("update" => $query)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    return true;
}


function updateText($id, $title, $author){
    echo "not implemented 1";
}

// TODO: put the right predictates
function createText($title, $author, $text){
    $uendpoint = 'http://localhost:3030/armalian/update';
    $id = uniqid();
    $uri = 'http://afel.insight-centre.org/armalian/doc/'.$id;
    $auri = 'http://afel.insight-centre.org/armalian/person/'.md5($author);
    $etext = base64_encode($text);
    $query = <<<QUERY
insert {
    graph <http://afel.insight-centre.org/armalian/tool> {
	<$uri> a <http://afel.insight-centre.org/armalian/Text> .
	<$uri> <http://afel.insight-centre.org/armalian/ID> "$id" .
	<$uri> <http://afel.insight-centre.org/armalian/title> "$title" .
	<$uri> <http://afel.insight-centre.org/armalian/author> <$auri> .
	<$auri> <http://afel.insight-centre.org/armalian/name> "$author" .
	<$uri> <http://afel.insight-centre.org/armalian/text> "$etext" 
    }
}
where {}
QUERY;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uendpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array("update" => $query)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    return $id;
}


?>
