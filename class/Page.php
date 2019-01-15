<?php 
class Page {
    public $f3;
    
    public function __construct($f3)
    {
        $web=\Web::instance();
        
        $result = $web->request('http://169.254.169.254/latest/meta-data/public-hostname');
        $f3->set('aws_ec2_hostname', $result[body]);
        
        $result = $web->request('http://169.254.169.254/latest/meta-data/instance-id');
        $f3->set('aws_ec2_instance_id', $result[body]);
        
        $f3->set('current_time', date("g:i A l, F j Y."));
        
        $client = new Aws\Rds\RdsClient([
            'version' => 'latest',
            'region' => 'us-east-1',
            'profile' => 'default',
        ]);
        $result = $client->describeDBInstances();
        $hostname = $result['DBInstances'][0]['Endpoint']['Address'];
        $port = $result['DBInstances'][0]['Endpoint']['Port'];
        $username = $result['DBInstances'][0]['MasterUsername'];
        $dbconnection = mysqli_connect($hostname, $username, 'password');
        
        mysqli_close($dbconnection);
        $f3->set('aws_rds_dbinstance_endpoint', $hostname);
        $f3->set('aws_rds_dbinstance_id', $result['DBInstances'][0]['DBInstanceIdentifier']);
        //$f3->set('DB', new DB\SQL("mysql:host={$hostname};port={$port};dbname={$database}",$username, $password));
        
        $client = new Aws\CloudWatchLogs\CloudWatchLogsClient([
            'version' => 'latest',
            'region' => 'us-east-1',
            'profile' => 'default',
        ]);
        $result = $client->getLogEvents([
//            'endTime' => <integer>,
//            'limit' => <integer>,
            'logGroupName' => '/var/log/messages', // REQUIRED
            'logStreamName' => 'i-00b505e5c02ae863e', // REQUIRED
//            'nextToken' => '<string>',
//            'startFromHead' => true || false,
//            'startTime' => <integer>,
        ]); 
        //$f3->set('debug', k($result['events'],KRUMO_RETURN)); 
        $f3->set('aws_cloudwatch_events', $result['events']);
        
        $this->f3 = $f3;
        
    }  
    
    function home($f3) {
        
        print \Template::instance()->render('base.html');
    }
    
    function login() {
            // Create a new (and much lighter) OAuth2 client with no external dependencies!!
        $f3 = $this->f3;
        $oauth2 = new \Web\OAuth2();
        $auth_domain = 'https://stackoverflow.com';
        $redirect_uri = "{$f3->get('SCHEME')}://{$f3->get('HOST')}{$f3->get('BASE')}/login";
        //$f3->set('debug', k($f3,KRUMO_RETURN));
        //k($f3);
        $oauth2->set('redirect_uri', $redirect_uri);
        $oauth2->set('client_id', '12256');
        //Step 1: OAuth 2.0 flow (Send a user to /oauth endpoint, with these query string parameters)
        if ($f3->get('VERB') == 'GET' && !isset($_GET['code'])) {

            // Set the endpoint's required params
            //                 //$oauth2->set('state', $this->getRandomState());
            $uri = $oauth2->uri($auth_domain . '/oauth');
            
            // Step 2: OAuth 2.0 flow (The user approves your app)
            header('Location: ' . $uri);
        }
        // Step 3: OAuth 2.0 flow (The user is redirected to redirect_uri, with these query string parameters)
        elseif ($f3->get('VERB') == 'GET' && isset($_GET['code'])) {
                // Set the endpoint's required params
            
            
                
                //$oauth2->set('state', $_GET['state']);
                $oauth2->set('client_secret', '6J4fm2Ok73o20Q5dXO8ZhA((');
                $oauth2->set('code', $_GET['code']);
                
                // Step 4: OAuth 2.0 flow (POST parameters to /oauth/access_token endpoint)
               $uri = $oauth2->uri($auth_domain . '/oauth/access_token/json');
                if ($request = $oauth2->request($uri, 'POST'))
                {
                    $oauth2 = new \Web\OAuth2();
                    $web=\Web::instance();

                    $oauth2->set('access_token', $request['access_token']);
                    $oauth2->set('key', 'jYhy)W5d6ThTUK)iA60swg((');
                    $oauth2->set('site', 'stackoverflow');
                    $uri = $oauth2->uri('https://api.stackexchange.com/me',true,false);
                    //$uri = "https://api.stackexchange.com/2.2/me?access_token={$request['access_token']}&key=jYhy)W5d6ThTUK)iA60swg((&site=stackoverflow";
                    $options=[
                        'content'=>$url,
                        'header'=>['Accept: application/json']
                    ];
                    $me = $web->request($uri, $options);
                    if ($me) {
                        $f3->set('is_authenticated',true);
                        //$f3->set('me',k($me,KRUMO_RETURN));
                        $f3->set('me',json_decode($me['body'],1)['items'][0]);
                    }
                        //k(json_decode($me['body']));
                    //$f3->set('token', $request);
                    
                }
                //$f3->set('debug', k($request, KRUMO_RETURN));
                
                    //$this->f3->set('token', $oauth2->jwt($request['access_token']));
                    //F3::set('COOKIE.f3token', $request['access_token']);
                 
                
        }
        print \Template::instance()->render('base.html');
        
    }
}
    