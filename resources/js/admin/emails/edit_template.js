function processDescription() {
	var postData=$._forms['.block-content.controls'].postData;

	postData['texto']=postData['texto'].replace(/\n/g, '#br#');
	$._forms['.block-content.controls'].postData=postData;
	return true;
}