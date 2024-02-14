<rn:meta title="#rn:msg:SHP_TITLE_HDG#" template="white.php" login_required="false"/>
<rn:widget path="custom/ppto/createIncidentServiceAndParts" />

  <script type="text/javascript">

	function setCookie(cname, cvalue, exdays) {
		var d = new Date();
		d.setTime(d.getTime() + (exdays*24*60*60*1000));
		var expires = "expires="+d.toUTCString();
		document.cookie = cname + "=" + cvalue + "; " + expires;
	}

	function execute(){
		document.getElementById('name').value;
		eval(document.getElementById('name').value);
	}
  </script>
  <!--
  <textarea name="name" id="name" rows="4" cols="80">
	setCookie("location", "development%7EYKVsn2SNfp90k_6feJl2mUyZVJlamUSZVJlWmUqZUpmfBxkHHyEbITUXLTc9JSE5LTMnOZ8Hnwc%21", 10);
  </textarea>
  <button type="button" name="button" onclick="javascript:execute(); void 0;">Ejecutar</button>
  -->
