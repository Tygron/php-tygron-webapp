let testCredentials = function(credentialsDom, callback, platform, username, password, mfa) {
	$credentialsDom = $(credentialsDom).closest('#credentials');
	$credentialsDom = $credentialsDom.length ? $credentialsDom : $(credentialsDom).closest('.credentials');
	$credentialsDom = $credentialsDom.length ? $credentialsDom : $(credentialsDom);

	let credentials = {
		'platform':platform,
		'username':username,
		'password':password,
		'mfa':mfa
	};

	for ( let i in credentials ) {
		if ( (!credentials[i]) && $credentialsDom.length ) {
			credentials[i] = $credentialsDom.find('[name="'+i+'"]').val();
		}
	}

	if ( !(credentials['platform'] && credentials['username'] && credentials['password']) ) {
		callback('missing','Enter credentials before validating');
		console.error('Credentials missing, could not validate');
		return;
	}

	if ( (!credentials['mfa']) || credentials.length == 0 ) {
		credentials['mfa'] = 'SMS';
	}

	var url = 'https://%platform%.tygron.com/api/myuser?f=JSON';
	url = url.replace('%platform%',credentials['platform']);
	let authHeader = [credentials['username'],credentials['password'],credentials['mfa']].join(':');

	setTimeout(function(){
		var xmlHttp = new XMLHttpRequest();

		xmlHttp.onreadystatechange = function() {
			if (xmlHttp.readyState == 4 ) {
				if( xmlHttp.status == 401 ) {
					if ( xmlHttp.response.indexOf( 'SMS code' )>=0 ) {
						callback('mfa', xmlHttp.response, xmlHttp);
					} else {
						callback('invalid', xmlHttp.response, xmlHttp);
					}
				} else if (xmlHttp.status == 403) {
					callback('blocked', 'Too many invalid requests. Unable to validate');
				} else if (xmlHttp.status == 200) {
					callback('valid', 'Credentials are valid', xmlHttp);
				}
			}
		}
		xmlHttp.open("GET", url);
		xmlHttp.setRequestHeader("Authorization", "Basic " + btoa(authHeader));

		xmlHttp.send(null);
	}, 1000);
}
