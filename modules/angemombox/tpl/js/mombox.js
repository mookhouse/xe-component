/**
 * @file   modules/board/js/board.js
 * @author NHN (developers@xpressengine.com)
 * @brief  board 모듈의 javascript
 **/

/* complete tp insert document */
function completeDocumentInserted(ret_obj)
{
	var error = ret_obj.error;
	var message = ret_obj.message;
	var mid = ret_obj.mid;
	var document_srl = ret_obj.document_srl;

	var url;
	if(!document_srl)
	{
		url = current_url.setQuery('mid',mid).setQuery('act','');
	}
	else
	{
		url = current_url.setQuery('mid',mid).setQuery('document_srl',document_srl).setQuery('act','');
	}
	location.href = url;
}