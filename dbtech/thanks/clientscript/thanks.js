
$.noConflict(true)(function($)
{var ftd=$(document),lastClick=0;thanksOptions.floodTime=parseInt(thanksOptions.floodTime);if(thanksOptions.vbversion>3)
{ftd.on('click','a[name=dbtech_thanks_downranked]',function(e)
{e.preventDefault();var postid=$(this).attr('data-postid');_displayPost(postid);});}
ftd.on('click','a[name=dbtech_thanks_button]',function()
{if(thanksOptions.floodTime>0)
{var d=new Date();var timenow=parseInt(d.getTime()/1000);if((timenow-lastClick)<thanksOptions.floodTime)
{alert(vbphrase['dbtech_thanks_must_wait_x_seconds'].replace('%time%',thanksOptions.floodTime).replace('%time2%',(timenow-lastClick)));return false;}
lastClick=timenow;}
var postid=$(this).attr('data-postid');if(typeof thanksOptions.thanksEntries[postid]=='undefined')
{thanksOptions.thanksEntries[postid]={};}
thanksOptions.thanksEntries[postid][$(this).attr('data-button')]=parseInt(thanksOptions.thanksEntries[postid][$(this).attr('data-button')])-1;_colourPost(postid);var extraParams={'securitytoken':SECURITYTOKEN,'do':'ajax','action':'entry','varname':$(this).attr('data-button'),'contenttype':thanksOptions.contenttype,'p':postid};var type='POST';var jqxhr=$.ajax({type:type,url:'thanks.php',data:(SESSIONURL?SESSIONURL+'&':'')+$.param(extraParams)}).done(function(data)
{var tagData=$(data),origData=data;data={thanksEntries:{},colorOptions:{}};var singleVals=['entries','actions','error'];if(tagData.find('colorOption').length)
{tagData.find('colorOption').each(function()
{var tagData2=$(this);if(typeof data.colorOptions[tagData2.attr('varname')]=='undefined')
{data.colorOptions[tagData2.attr('varname')]={};}
data.colorOptions[tagData2.attr('varname')][tagData2.attr('numclicks')]={color:tagData2.text(),settings:tagData2.attr('settings')};});}
if(tagData.find('thanksEntry').length)
{tagData.find('thanksEntry').each(function()
{data.thanksEntries[$(this).text()]=$(this).attr('numclicks');});}
for(var i in singleVals)
{data[singleVals[i]]='';if(tagData.find(singleVals[i]).length)
{data[singleVals[i]]=tagData.find(singleVals[i]).text();}
else if(singleVals[i]!='error')
{data['error']=origData;}}
if(data.error)
{alert(data.error);console.error(timeStamp()+"AJAX Error: %s",data.error);return true;}
$('#dbtech_thanks_entries_'+postid).html(data.entries);$('#dbtech_thanks_actions_'+postid).html(data.actions);if(typeof data.thanksEntries!='undefined')
{thanksOptions.thanksEntries[postid]=data.thanksEntries;switch(thanksOptions.contenttype)
{case'post':_colourPost(postid);_displayPost(postid);break;case'blog':break;}}
if(data.colorOptions)
{thanksOptions.colorOptions=data.colorOptions;}});});if(thanksOptions.contenttype=='post')
{for(var postId in thanksOptions.thanksEntries)
{_colourPost(postId);}}
function _colourPost(postid)
{if(typeof thanksOptions.thanksEntries[postid]=='undefined')
{return false;}
var highestThreshold=0,highestObj;for(var varName in thanksOptions.colorOptions)
{if(typeof thanksOptions.thanksEntries[postid][varName]=='undefined')
{continue;}
thanksOptions.thanksEntries[postid][varName]=parseInt(thanksOptions.thanksEntries[postid][varName]);for(var threshold in thanksOptions.colorOptions[varName])
{threshold=parseInt(threshold);if(thanksOptions.thanksEntries[postid][varName]>=threshold&&threshold>highestThreshold)
{highestThreshold=threshold;highestObj=thanksOptions.colorOptions[varName][threshold];}}}
if(thanksOptions.vbversion==3)
{$('#post'+postid+' tbody td').each(function(index,element)
{if($(this).is('.alt1, .alt2'))
{$(this).addClass(function(index,currentClass)
{var exploded=currentClass.split(' ');for(var i in exploded)
{if(exploded[i].toString().match(/\bthanks_highlight_/))
{$(this).removeClass(exploded[i]);}}
return(highestThreshold>0?'thanks_highlight_'+highestObj['color']:'');});}});}
else
{$('#post_'+postid+' .posthead,#post_'+postid+' .postfoot .textcontrols,#post_'+postid+' .postdetails,#post_'+postid+' .postdetails .userinfo,#post_'+postid+' .postdetails .userinfo_noavatar,#post_'+postid+' .postbody').addClass(function(index,currentClass)
{var exploded=currentClass.split(' ');for(var i in exploded)
{if(exploded[i].toString().match(/\bthanks_highlight_/))
{$(this).removeClass(exploded[i]);}}});if(highestThreshold>0)
{if(searchString=='')
{return'';}
$(searchString).addClass(function(index,currentClass)
{var exploded=currentClass.split(' ');for(var i in exploded)
{if(exploded[i].toString().match(/\btextcontrols/))
{$('.postbitlegacy .postfoot .textcontrols span a').css('background-color','transparent');$('.postbitlegacy .postfoot .textcontrols span.seperator').css('border-right','1px #000000 solid');}}
return'thanks_highlight_'+highestObj['color'];});}}
if(highestThreshold>0)
{console.log('Colouring post '+postid+' with colour '+highestObj['color']);return true;}}
function _displayPost(postid)
{var postCount=$('#postcount'+postid).attr('name');var extraParams={'securitytoken':SECURITYTOKEN,'p':postid,'postcount':(typeof postCount!='undefined'?parseInt(postCount):thanksOptions.postCount)};var type='POST';var jqxhr=$.ajax({type:type,url:'showpost.php',data:(SESSIONURL?SESSIONURL+'&':'')+$.param(extraParams)}).done(function(data)
{var postbit=$('postbit',data).text();if(thanksOptions.vbversion==3)
{var postWrapper=$('#post'+postid);}
else
{var postWrapper=$('#post_'+postid);}
if(postbit)
{var newpostbit=string_to_node(postbit);postWrapper.replaceWith(newpostbit);PostBit_Init(newpostbit,postid);_colourPost(postid);}});return false;};function timeStamp()
{var d=new Date();return'['+d.getHours()+':'+d.getMinutes()+':'+d.getSeconds()+'] ';};});