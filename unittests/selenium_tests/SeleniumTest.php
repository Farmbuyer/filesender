<?php

require_once 'vendor/autoload.php';

/**
 * Created by PhpStorm.
 * User: peter
 * Date: 30-5-16
 * Time: 15:42
 */
class SeleniumTest extends Sauce\Sausage\WebDriverTestCase
{
    protected $start_url_path = '';

    public static $browsers = array(
        // run FF15 on Windows 8 on Sauce
//        array(
//            'browserName' => 'firefox',
//            'desiredCapabilities' => array(
//                'version' => '15',
//                'platform' => 'Windows 2012',
//            )
//        ),
        // run Chrome on Linux on Sauce
        array(
            'browserName' => 'chrome',
            'desiredCapabilities' => array(
                'platform' => 'Linux'
            )
        ),
        // run Mobile Safari on iOS
        //array(
        //'browserName' => '',
        //'desiredCapabilities' => array(
        //'app' => 'safari',
        //'device' => 'iPhone Simulator',
        //'version' => '6.1',
        //'platform' => 'Mac 10.8',
        //)
        //)//,
        // run Chrome locally
        //array(
        //'browserName' => 'chrome',
        //'local' => true,
        //'sessionStrategy' => 'shared'
        //)
    );

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        require_once('includes/init.php');

        if(getenv('SAUCE_USERNAME') === false)
        {

            if (Config::get('sauce_username') == null || Config::get('sauce_access_key') == null) {
                die('Sauce credentials not set!');
            }
            putenv('SAUCE_USERNAME='.Config::get('sauce_username'));
            putenv('SAUCE_ACCESS_KEY='.Config::get('sauce_access_key'));

        }

        $this->start_url = Config::get('site_url');

        if($this->start_url_path != '') {
            $this->start_url = Config::get('site_url') . '/' . $this->start_url_path;
        }


        parent::__construct($name, $data, $dataName);


    }

    public function setUpPage()
    {
        // set the method which knows if this is a file we're trying to upload
        $this->fileDetector(function($filename) {

            $base_path = base_path();
            if(substr($filename, 0, strlen($base_path)) != $base_path)
            {
                $filename = base_path($filename);
            }

            if(file_exists($filename)) {
                return $filename;
            } else {
                return NULL;
            }
        });

        return parent::setUpPage();
    }

    protected function getKeyBindings()
    {
        $key_bindings = [];
        
        $refl = new ReflectionClass('PHPUnit_Extensions_Selenium2TestCase_Keys');
        foreach ($refl->getConstants() as $constant_key=>$constant_value)
        {
            $key_bindings[strtolower($constant_key)] = $constant_value;
        }

        return $key_bindings;
    }

    protected function setupUnauthenticated()
    {
        $this->changeAuthSPType('saml');
        $this->refresh();
    }

    protected function setupAuthenticated()
    {
        $this->changeAuthSPType('fake');
        $this->refresh();
    }

    private function changeAuthSPType($type)
    {
        //read the entire string
        $str=file_get_contents('config/config.php');

        //replace something in the file string
        $str=preg_replace("/\$config\['auth_sp_type'\] = '(.*)';/", "\$config\['auth_sp_type'\] = '$type';",$str);

        //write the entire string
        file_put_contents('msghistory.txt', $str);
    }

}