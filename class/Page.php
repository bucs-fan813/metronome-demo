<?php 
class Page {
	//Declare globals
    public $f3;
    private $aws_credentials;
    private $instance_id;
    
    public function __construct($f3)
    {
		$this->aws_credentials = [
            'version' => 'latest',
            'region' => 'us-east-1',
            'profile' => 'default'
        ];
		
		//Get current instance ID
		$web=\Web::instance();
		
		$this->instance_id = $web->request('http://169.254.169.254/latest/meta-data/instance-id')['body'];
        $this->f3 = $f3;
    }  
    
    function home() {
        print \Template::instance()->render('base.html');
    }
    
    function login() {
    	//Bring $f3 into scope
        $f3 = $this->f3;
        
        //Set variables
        $f3->set('current_time', self::get_current_time());
        $f3->set('aws_ec2_instance', self::get_ec2_instance($f3));
        $f3->set('aws_rds_instance', self::get_rds_instance($f3));
        $f3->set('aws_cloudwatch', self::get_cloudwatch_data($f3));
        $f3->set('oauth_user', self::get_oauth_user($f3)); 
		//Render Page
        print \Template::instance()->render('base.html');
    }
    
    function get_oauth_user($f3) {
    	 
    	// Create a new (and much lighter) OAuth2 client with no external dependencies!!  	   
    	$oauth2 = new \Web\OAuth2();
    	$oauth_credentials = $f3->get('oauth_credentials');
    	$auth_domain = $oauth_credentials['oauth_domain'];
    	$redirect_uri = "{$f3->get('SCHEME')}://{$f3->get('HOST')}{$f3->get('BASE')}/login";
    	
    	//Set OAuth parameters
    	$oauth2->set('redirect_uri', $redirect_uri);
    	$oauth2->set('client_id', $oauth_credentials['oauth_client_id']);
    	$oauth2->set('client_secret', $oauth_credentials['oauth_client_secret']);

    	if ($f3->get('VERB') == 'GET' && !isset($_GET['code'])) {
    		//Step 1: OAuth 2.0 flow (Send a user to /oauth endpoint, with these query string parameters)
    		header('Location: ' . $oauth2->uri($auth_domain . '/oauth'));
    		// Step 2: OAuth 2.0 flow (The user approves your app)
    	}
    	elseif ($f3->get('VERB') == 'GET' && isset($_GET['code'])) {
    		// Step 3: OAuth 2.0 flow (The user is redirected to redirect_uri, with these query string parameters)
    		$oauth2->set('code', $_GET['code']);    	
    		// Step 4: OAuth 2.0 flow (POST parameters to /oauth/access_token endpoint)
    		$uri = $oauth2->uri($auth_domain . '/oauth/access_token/json');
    		$request = $oauth2->request($uri, 'POST');    		
    		if (!array_key_exists('error_message',$request))
    		{
    			$oauth2 = new \Web\OAuth2();
    			$web=\Web::instance();
    			$oauth2->set('access_token', $request['access_token']);
    			$oauth2->set('key', $oauth_credentials['oauth_stackoverflow_key']);
    			$oauth2->set('site', $oauth_credentials['oauth_stackoverflow_site']);
    			$uri = $oauth2->uri('https://api.stackexchange.com/me',true,false);
    			//$uri = "https://api.stackexchange.com/2.2/me?access_token={$request['access_token']}&key=jYhy)W5d6ThTUK)iA60swg((&site=stackoverflow";
    			$options=[
    					//'content'=>$url,
    					'header'=>['Accept: application/json']
    			];
    			$me = $web->request($uri, $options);
    			if ($me) {
    				$f3->set('is_authenticated',true);
    				return json_decode($me['body'],1)['items'][0];
    			}
    		}
    		$f3->set('is_authenticated',false);
    }
   }
    
    //Get aws_ec2_instance
    function get_ec2_instance (&$f3) {    	
    	//Create EC2 Client and perform query
    	$client = new Aws\Ec2\Ec2Client($this->aws_credentials);
    	$result = $client->describeInstances([
    			'InstanceIds' => [$this->instance_id]
    	]);

    	//Set aws_ec2_instance properties
		$output = [
				//'Name' => $result['Reservations'][0]['Instances'][0]['Tags'][0]['Value'],
    			'Address' => $result['Reservations'][0]['Instances'][0]['PublicDnsName'],
				'VpcId' => $result['Reservations'][0]['Instances'][0]['VpcId'],
		        'Identifier' => $this->instance_id
    	];
		foreach ($result['Reservations'][0]['Instances'][0]['Tags'] as $tag)
			if ($tag['Key'] == 'Name')
				$output['Name'] = $tag['Value'];
				
		$result = $client->DescribeVpcs([
				'VpcIds' => [$output['VpcId']]
		]);
		
		//Get sensitive OAuth Credentials from VPC tags. This is not the AWS best practice but for demonstration purposes we will
		//use this method to keep the code UNCLASSIFIED and demonstrate portability with M.A.D.E!
		$oauth_credentials = [];
		foreach ($result['Vpcs'][0]['Tags'] as $tag)
			$oauth_credentials[$tag['Key']] = $tag['Value'];

		$f3->set('oauth_credentials', $oauth_credentials);				
		$output['style'] = ($output['Name'] == 'pulser_web_1') ? 'badge-primary' : 'badge-success';
    	return $output;
    }

    //Set current_time
    function get_current_time () {return  date("g:i A l, F j Y.");}

    //Get aws_rds_instance    
    function get_rds_instance () {
    	//Create RDS Client
        $client = new Aws\Rds\RdsClient($this->aws_credentials);
        $result = $client->describeDBInstances();       
        
        //Set aws_rds_instance properties
        $output = [
        	'Address' => $result['DBInstances'][0]['Endpoint']['Address'],
        	'Port' => $result['DBInstances'][0]['Endpoint']['Port'],
        	'MasterUsername' => $result['DBInstances'][0]['MasterUsername'],
        	'Identifier' => $result['DBInstances'][0]['DBInstanceIdentifier'],
        	'ARN' =>$result['DBInstances'][0]['DBInstanceArn']
        ];
        
        $result = $client->ListTagsForResource([
        	'ResourceName' => $output['ARN']
        ]);
        
        foreach ($result['TagList'] as $tag)
        	if ($tag['Key'] == 'RDS_Password')
        		$output['RDS_Password'] = base64_decode($tag['Value']);
               		
        $output['connection'] = mysqli_connect($output['Address'], $output['MasterUsername'], $output['RDS_Password']); 	
		mysqli_close($output['connection']);
        return $output;
        //$f3->set('DB', new DB\SQL("mysql:host={$hostname};port={$port};dbname={$database}",$username, $password));
    }

    //Get aws_cloudwatch data
    function get_cloudwatch_data () {
       $client = new Aws\CloudWatchLogs\CloudWatchLogsClient($this->aws_credentials);
       
       $result = $client->describeLogStreams([
           //'descending' => true || false,
           //'limit' => <integer>,
           'logGroupName' => '/var/log/httpd/access_log', // REQUIRED
           'logStreamNamePrefix' => $this->instance_id,
           //'nextToken' => '<string>',
           //'orderBy' => $this->instance_id,
       ]);
       
       //Create log stream if it doesnt exist for this instance
       if (empty($result['logStreams'])) {
           $client->createLogStream([
               'logGroupName' => '/var/log/httpd/access_log', // REQUIRED
               'logStreamName' => $this->instance_id, // REQUIRED
           ]);
       }
       
       $result = $client->getLogEvents([
//            'endTime' => <integer>,
            'limit' => 100,
            'logGroupName' => '/var/log/httpd/access_log', // REQUIRED
            'logStreamName' => $this->instance_id, // REQUIRED //
//            'nextToken' => '<string>',
//            'startFromHead' => true || false,
//            'startTime' => <integer>,
        ]);

       $output['logs']=$result;
       
       $client = new Aws\CloudWatch\CloudWatchClient($this->aws_credentials); 
       $result = $client->describeAlarms([
           //'ActionPrefix' => '<string>',
           //'AlarmNamePrefix' => '<string>',
           //'AlarmNames' => ['<string>', ...],
           'MaxRecords' => 100,
           //'NextToken' => '<string>',
           'StateValue' => 'ALARM',
       ]);
       
       $output['alarms']=$result;
       return $output; 
    }
}