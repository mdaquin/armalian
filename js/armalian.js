// TODO:
//   - edit annotations
//   - autocompletion on everything

var tids = {};
var authors = [];
var arguments = [];
var statements = [];

var currentText = null;
var currentTextText = null;
var currentSelection = {};
var currentColumn = "right";

var annotations = {
    arguments: [],
    propositions: [],
    mops: []
}

$( document ).ready(function() {
    // TODO add autocomplete
    $.ajax({
	url: "http://localhost/armalian/api/",
	success: function (data, ts){	
	    console.log(data);
	    var d = JSON.parse(data);
	    console.log(d);
	    tids=d.texts;
	}
    });
    $("#loadtext").click(function(){
	showLoadText();
    });
    $("#newtext").click(function(){
	showNewText();
    });
    $("#ntbutton").click(function(){
	closeNewText();
	newTextAction();
    });
    $("#ntcbutton").click(function(){
	closeNewText();
    });
    $("#ltcbutton").click(function(){
	closeLoadText();
    }); 
    $("#afbutton").hover(function(){
	showTip("aftip");
    }, function(){
	closeTip("aftip");
    }); 
    $("#statbutton").hover(function(){
	showTip("stattip");
    }, function(){
	closeTip("stattip");
    }); 
    $("#argbutton").hover(function(){
	showTip("argtip");
    }, function(){
	closeTip("argtip");
    }); 
    $("#argbutton").click(function(){
	closeAnnotationButtons();	
	showArgumentDialog();
    });
    $("#npropcbutton").click(function(){
	closePropDialog();
    });
    $("#npropbutton").click(function(){
	if (newPropAction())
	    closePropDialog();
    });
    $("#nargcbutton").click(function(){
	closeArgumentDialog();
    });
    $("#nargbutton").click(function(){
	if (newArgumentAction())
	    closeArgumentDialog();
    });
    $("#statbutton").click(function(){
	closeAnnotationButtons();	
	showPropDialog();
    });
    $("#afbutton").click(function(){
	closeAnnotationButtons();	
	showAformDialog();
    });
    $("#afcbutton").click(function(){
	closeAformDialog();
    });
    $("#nafbutton").click(function(){
	if (newAformAction())
	    closeAformDialog();
    });
    document.addEventListener("selectionchange", function(){
	var sel = window.getSelection();
	var len = sel.focusOffset-sel.anchorOffset;
	if (len>3){
	    currentSelection.start = sel.anchorOffset;
	    currentSelection.lenght = len;
	    currentSelection.text = sel.anchorNode.data.substr(currentSelection.start, currentSelection.lenght);
	    console.log(currentSelection);
	    showAnnotationButtons();
	}
    });
});

function newTextAction(){
    var title = $("#titletext").val();
    var author = $("#authortext").val();
    var text = $("#texttext").val();
    var url = "http://localhost/armalian/api/";
    var data = {'title': title, 'author':author, 'text': text};
    $.ajax({
	type: "POST",
	url: url,
	data: data,
	success: function (data, ts){	
	    var d = JSON.parse(data);
	    if (d.status == "success"){
		console.log("text created "+d.id);
		tids[d.id] = title;
		authors.push(author);
		currentText = d.id;
		currentTextText = text;
		$("#middlecolumn").text(text);
		$("#leftcolumn").html("");
		$("#rightcolumn").html("");
		annotations.arguments = [];
		annotations.propositions = [];
		annotations.mops = [];
	    }
	}
    });
}

function closeNewText(){
    $("#ntdialog").css("display", "none");
}

function showNewText(){
    $("#ntdialog").css("display", "block");
    $("#titletext").val("");
    $("#authortext").val("");
    $("#texttext").val("");
}

function showLoadText(){
    var st = '';
    for(var t in tids){
	st += '<div class="ltonetext" onclick="loadTextAction(\''+t+'\');">'+tids[t]+'</div>';
    }
    $("#textlist").html(st);
    $("#ltdialog").css("display", "block");
}

function closeLoadText(){
    $("#ltdialog").css("display", "none");
}

function loadTextAction(tid){
    $.ajax({
	url: "http://localhost/armalian/api/?id="+tid,
	success: function (data, ts){	
	    var d = JSON.parse(data);
	    console.log(d);
	    currentText = d.id;
	    currentTextText = d.text;
	    $("#middlecolumn").text(d.text);
	    $("#leftcolumn").html("");
	    $("#rightcolumn").html("");
	    annotations.arguments = d.arguments;
	    annotations.propositions = d.propositions;
	    annotations.mops = d.mops;
	    updateTextDisplay();
	}
    });
    closeLoadText();
}

function showAnnotationButtons(){
    $("#nabuttons").css("display", "block");
}

function closeAnnotationButtons(){
    $("#nabuttons").css("display", "none");
}

function showTip(eid){
    $("#"+eid).css("display", "block");
}

function closeTip(eid){
    $("#"+eid).css("display", "none");
}

function closeArgumentDialog(){
    $("#argdialog").css("display", "none");
}

function closePropDialog(){
    $("#propdialog").css("display", "none");
}


function closeAformDialog(){
    $("#afdialog").css("display", "none");
}

function showArgumentDialog(){
    $("#argumenttext").val("");
    $("#adesctext").val("");
    $("#asubjecttext").val("");
    $("#aformtext").val("");
    $("#argdialog").css("display", "block");
}

function showPropDialog(){
    $("#proptext").val("");
    $("#pdesctext").val("");
    $("#psubjecttext").val("");
    $("#propdialog").css("display", "block");
}

function showAformDialog(){
    $("#aftext").val("");
    $("#afdialog").css("display", "block");
}

function newArgumentAction(){
    var argt = $("#argumenttext").val();
    if (argt && argt != "") {
	var na = {};
	na.argument = argt;
	na.description = $("#adesctext").val();
	na.subject = $("#asubjecttext").val();
	na.argumform = $("#aformtext").val();
	na.type = "argument";
	na.tid = currentText;
	na.selection = JSON.parse(JSON.stringify(currentSelection));
	console.log(na);
	var url = "http://localhost/armalian/api/";
	var data = {'na': na, 'type': 'argument', 'id': currentText};
	$.ajax({
	    type: "POST",
	    url: url,
	    data: data,
	    success: function (data, ts){	
		console.log(data);
		var d = JSON.parse(data);
		if (d.status == "success"){
		    console.log("argument created");
		    annotations.arguments.push(na);
		    updateTextDisplay();
		}
	    }
	});
	return true;
    }
    return false;
}

function newPropAction(){
    var argt = $("#proptext").val();
    if (argt && argt != "") {
	var na = {};
	na.proposition = argt;
	na.description = $("#pdesctext").val();
	na.subject = $("#psubjecttext").val();
	na.type = "proposition";
	na.tid = currentText;
	na.selection = JSON.parse(JSON.stringify(currentSelection));
	console.log(na);
	var url = "http://localhost/armalian/api/";
	var data = {'na': na, 'type': 'proposition', 'id': currentText};
	$.ajax({
	    type: "POST",
	    url: url,
	    data: data,
	    success: function (data, ts){	
		console.log(data);
		var d = JSON.parse(data);
		if (d.status == "success"){
		    console.log("proposition created");
		    annotations.propositions.push(na);
		    updateTextDisplay();
		}
	    }
	});
	return true;
    }
    return false;
}

function newAformAction(){
    var argt = $("#aftext").val();
    if (argt && argt != "") {
	var na = {};
	na.mop = argt;
	na.tid = currentText;
	na.selection = JSON.parse(JSON.stringify(currentSelection));
	console.log(na);
	var url = "http://localhost/armalian/api/";
	var data = {'na': na, 'type': 'mop', 'id': currentText};
	$.ajax({
	    type: "POST",
	    url: url,
	    data: data,
	    success: function (data, ts){	
		console.log(data);
		var d = JSON.parse(data);
		if (d.status == "success"){
		    console.log("mode of persuasion created");
		    annotations.mops.push(na);
		    updateTextDisplay();
		}
	    }
	});
	return true;
    }
    return false;
}


function updateTextDisplay(){
    var ntext = currentTextText;
    for (var i in annotations.arguments){
	ntext = ntext.replace(annotations.arguments[i].selection.text, '<span class="argumentintext" id="argument_'+i+'">'+annotations.arguments[i].selection.text+'</span>');
    }
    for (var i in annotations.propositions){
	ntext = ntext.replace(annotations.propositions[i].selection.text, '<span class="propositionintext" id="proposition_'+i+'">'+annotations.propositions[i].selection.text+'</span>');
    }
    for (var i in annotations.mops){
	ntext = ntext.replace(annotations.mops[i].selection.text, '<span class="aformintext" id="mop_'+i+'">'+annotations.mops[i].selection.text+'</span>');
    }
    $("#middlecolumn").html(ntext);
    var left = false;
    $("#leftcolumn").html("");
    $("#rightcolumn").html("");
    $(".argumentintext").each(function (){
	var top = $(this).position().top;
	var left = "3%";
	if (left==false) left = "80%";
	var id = parseInt($(this).attr("id").substring(9));
	var st = '<div class="argumentdisplay" style="position:absolute; top: '+top+'px; left: '+left+'">'+annotations.arguments[id].argument+'</div>';
	if (left==true)
	    $("#leftcolumn").append(st);
	else 
	    $("#rightcolumn").append(st);
	left=!left;
    });
    $(".propositionintext").each(function (){
	var top = $(this).position().top;
	var left = "3%";
	if (left==false) left = "80%";
	var id = parseInt($(this).attr("id").substring(12));
	var st = '<div class="propositiondisplay" style="position:absolute; top: '+top+'px; left: '+left+'">'+annotations.propositions[id].proposition+'</div>';
	if (left==true)
	    $("#leftcolumn").append(st);
	else 
	    $("#rightcolumn").append(st);
	left=!left;
    });
    $(".aformintext").each(function (){
	var top = $(this).position().top;
	var left = "3%";
	if (left==false) left = "80%";
	var id = parseInt($(this).attr("id").substring(4));
	var st = '<div class="aformdisplay" style="position:absolute; top: '+top+'px; left: '+left+'">'+annotations.mops[id].mop+'</div>';
	if (left==true)
	    $("#leftcolumn").append(st);
	else 
	    $("#rightcolumn").append(st);
	left=!left;
    });
}
