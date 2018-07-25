
$.noConflict(true)(function($)
{var currentTab;$('a[name="thankstab"]').on('click',function(e)
{e.preventDefault();var thisTab=$(this),tabList=thisTab.parent().parent().children('dd'),currentTab;tabList.each(function(index,element)
{if($(element).hasClass('selected'))
{currentTab=$(element).children('a').attr('data-tabid');return true;}});if(thisTab.attr('data-tabid')==currentTab)
{return true;}
$('a[name="thankstab"][data-tabid="'+currentTab+'"]').parent().removeClass('selected');$('div[name="thanksview"][data-tabid="'+currentTab+'"]').fadeOut('fast')
currentTab=thisTab.attr('data-tabid');thisTab.parent().addClass('selected');$('div[name="thanksview"][data-tabid="'+currentTab+'"]').fadeIn('fast').promise().done(function()
{$('select[name="varname"]').trigger('change');});});$('select[name="varname"]').on('change',function()
{var val=$(this).val(),blockId=$(this).attr('data-blockid');$('ul[name="display"][data-varname!="'+val+'"][data-blockid="'+blockId+'"]').fadeOut('fast').promise().done(function()
{$('ul[name="display"][data-varname="'+val+'"][data-blockid="'+blockId+'"]').fadeIn('fast');});});$('select[name="varname"]').each(function(index,element)
{$('ul[name="display"][data-varname="'+$(this).val()+'"][data-blockid="'+$(this).attr('data-blockid')+'"]').fadeIn('fast');});});