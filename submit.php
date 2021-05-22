<?php // Process form submission and integrate with Mailchimp API

$status_array = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// Handle unfinished form. Remember to perform additional suitable validation here
	$email = $_POST["email"];	

	if (!isset($email) && $email == '') {
		// Send user back to the form
		$status_array['status'] = 0;
		$status_array['msg'] = 'Please enter your email!!';	    
		echo json_encode($status_array);
		exit;
	}
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$status_array['status'] = 0;
		$status_array['msg'] = 'Please enter valid email!!';	    
		echo json_encode($status_array);
		exit;
	}


	// Set default to handle if user wants to subscribe or not
	$subscribed = false;
	$_POST['newsletter'] = true;
	// Check user has accepted to sign up to the newsletter
	if (isset($_POST['newsletter'])) {
		// Set API credentials and build URL
		$data_center = 'us2';
		$audience_id = '90678b668d';
		$api_key = 'f3ea0ad34f237115f1ed1c4669dc4992-us2';
		$url = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $audience_id . '/members';

		// Build user details array to send
		$user_details = [
		'email_address' => $_POST['email'],
		'status' => 'subscribed'
		];
		$user_details = json_encode($user_details);

		// Send POST request with cURL
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_USERPWD, 'newsletter:' . $api_key);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $user_details);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json',
		'Content-Length: ' . strlen($user_details)
		]);
		$result = curl_exec($ch);
		$result_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($result_code === 200) {
			$subscribed = true;
		}
	}
	if ($subscribed) {
		$status_array['status'] = 1;
		$status_array['msg'] = 'You have been subscribed!';
		// the message
		$msg = "Dear User,\nThanks for your interest! We will be in touch soon!";
		// use wordwrap() if lines are longer than 70 characters
		$msg = wordwrap($msg,70);
		// send email
		
        $headers = "From: hello@getwave.xyz <hello@getwave.xyz> \r\n".
        'Reply-To: hello@getwave.xyz' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
        

		
		mail($_POST['email'],"You have been subscribed!",$msg,$headers);
		echo json_encode($status_array);
		exit;
	} else {
		$status_array['status'] = 0;
		$status_array['msg'] = 'Something went wrong!';	    
		echo json_encode($status_array);
		exit;		
	}
}
echo json_encode($status_array);
exit;