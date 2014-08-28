<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 7/22/14
 * Time: 10:04 AM
 */

/**
 * Ldap Datasource
 *
 * @package datasources
 * @subpackage datasources.models.datasources
 */
class CanItSource extends DataSource {

    /**
     * Datasource description
     *
     * @var string
     * @access public
     */
    public $description = 'A datasource for accessing the CanIt API Client';

    public $cookie;
    public $curl_headers;
    public $is_error;
    public $last_error;
    public $curl_content_type;
    public $curl_content;

    public function __construct($config = null) {
        parent::__construct($config);
    }

    /**
     * Get the last API-related error message
     * @return string
     */
    function get_last_error () {
        return $this->last_error;
    }

    /**
     * Returns true if the last API call failed,
     * false if it succeeded
     * DEPRECATED: You should use the succeeded() function instead
     */
    function is_error() {
        return $this->is_error;
    }

    /**
     * Returns true if the last API call succeeded,
     * false if it failed
     */
    function succeeded() {
        return ! $this->is_error;
    }

    public function read(Model $model, $queryData = array(), $recursive = null) {
        $method = strtoupper($queryData['method']);
        $conditions = $queryData['conditions'];
        $query = $queryData['query'];

        $rel_url = '';
        foreach($conditions as $key => $val) {
            if (is_numeric($key)) {
                $rel_url .= $val . '/';
            } else {
                if (is_array($val)) {
                    $rel_url .= $key . '/';
                    foreach($val as $v) {
                        $rel_url .= $v . '/';
                    }
                } else {
                    $rel_url .= $key . '/' . $val . '/';
                }
            }
        }
        $rel_url = rtrim($rel_url, '/');

        $this->login();
        switch($method) {
            case 'GET':
                $result = $this->do_get($rel_url, $query);
                break;
            case 'POST':
                $result = $this->do_post($rel_url, $query);
                break;
            case 'DELETE':
                $result = $this->do_delete($rel_url, $query);
                break;
            case 'PUT':
                $result = $this->do_put($rel_url, $query);
                break;
            default:
                $result = false;
        }
        $this->logout();

        return $result;
    }

    /**
     * Log in to the API
     * @return boolean True on successful login; false otherwise
     */
    public function login() {
        $this->do_post('login', array(
            'user' => $this->config['login'],
            'password' => $this->config['password']
        ));
        if ($this->is_error) {
            return false;
        }

        # Set cookie
        foreach ($this->curl_headers as $header) {
            if (strpos($header, ':') !== false) { //TODO: Make FALSE if necessary
                list($name, $val) = explode(':', $header, 2);
            } else {
                $name = $header;
                $val = '';
            }
            $name = strtolower(trim($name));
            $val = trim($val);
            if ($name == 'set-cookie') {
                if ($this->cookie != '') {
                    $this->cookie .= '; ';
                }
                $this->cookie .= $val;
            }
        }
        return true;
    }

    /**
     * Log out of the API
     */
    public function logout() {
        $this->do_get('logout');
        $this->cookie = '';
    }

    /**
     * Do a GET request
     * @param string $rel_url The relative URL.  That is everything
     *                        AFTER the /canit/api/2.0/ part of the full
     *                        URL.
     * @param array $params   Search array converted to ?key1=val1&key2=val2...
     * @return NULL on failure, a PHP data structure on success.
     */
    public function do_get($rel_url, $params = null) {
        # If $rel_url begins with a slash, remove it
        $rel_url = ltrim($rel_url, '/');

        $scheme = $this->config['uri']['scheme'];
        $host = $this->config['uri']['host'];
        $base_url = $this->config['basePath'];

        $full_url = "$scheme://$host/$base_url/$rel_url";
        if (is_array($params)) {
            $first_time = 1;
            foreach ($params as $key => $val) {
                if ($first_time) {
                    $full_url .= '?';
                    $first_time = 0;
                } else {
                    $full_url .= '&';
                }
                $full_url .= urlencode($key) . '=' . urlencode($val);
            }
        }
        $ch = curl_init();
        $this->curl_call($full_url, $ch);
        curl_close($ch);

        return $this->deserialize_curl_data();
    }

    /**
     * Do a PUT request
     * @param string $rel_url The relative URL.  That is everything
     *                        AFTER the /canit/api/2.0/ part of the full
     *                        URL.
     * @param array $put_data An associative array of key/value pairs.
     * @return void Nothing useful; check $this->is_error() to test success
     */
    public function do_put($rel_url, $put_data) {
        # If $rel_url begins with a slash, remove it
        $rel_url = ltrim($rel_url, '/');

        $scheme = $this->config['uri']['scheme'];
        $host = $this->config['uri']['host'];
        $base_url = $this->config['basePath'];

        $full_url = "$scheme://$host/$base_url/$rel_url";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        $encoded = json_encode($put_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($encoded)));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        $this->curl_call($full_url, $ch);
        curl_close($ch);

        return $this->deserialize_curl_data();
    }

    /**
     * Do a DELETE request
     * @param string $rel_url The relative URL.  That is everything
     *                        AFTER the /canit/api/2.0/ part of the full
     *                        URL.
     * @return void Nothing useful; check $this->is_error() to test success
     */
    public function do_delete($rel_url) {
        # If $rel_url begins with a slash, remove it
        $rel_url = ltrim($rel_url, '/');

        $scheme = $this->config['uri']['scheme'];
        $host = $this->config['uri']['host'];
        $base_url = $this->config['basePath'];

        $full_url = "$scheme://$host/$base_url/$rel_url";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->curl_call($full_url, $ch);
        curl_close($ch);

        return NULL;
    }

    /**
     * Do a POST request
     * @param string $rel_url The relative URL.  That is everything
     *                        AFTER the /canit/api/2.0/ part of the full
     *                        URL.
     * @param array $post_data An associative array of key/value pairs.
     * @return void Nothing useful; check $this->is_error() to test success
     */
    public function do_post($rel_url, $post_data) {
        # If $rel_url begins with a slash, remove it
        $rel_url = ltrim($rel_url, '/');

        $scheme = $this->config['uri']['scheme'];
        $host = $this->config['uri']['host'];
        $base_url = $this->config['basePath'];

        $full_url = "$scheme://$host/$base_url/$rel_url";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $this->curl_call($full_url, $ch);
        curl_close($ch);

        return $this->deserialize_curl_data();
    }

    private function curl_call($url, $ch) {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1200);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        if ($this->cookie != '') {
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:', 'Accept: application/json'));
        $result = curl_exec($ch);
        if ($result === false) {
            $this->is_error = 1;
            $this->last_error = curl_error($ch);
        } else {
            $arr = explode("\r\n\r\n", $result, 3);
            $this->curl_headers = explode("\r\n", $arr[0]);
            $this->curl_content = $arr[1];
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($code >= 200 && $code <= 299) {
                $this->curl_content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $this->is_error = 0;
                $this->last_error = '';
            } elseif ($code >= 400 && $code <= 599) {
                $this->is_error = 1;
                $this->set_error_from_result($this->curl_content);
            } else {
                $this->is_error = 1;
                $this->last_error = "Unknown HTTP response $code";
            }
        }
    }

    private function set_error_from_result ($result) {
        # Special case: Login failures always come back in YAML
        # in old/buggy versions of API... sigh.
        if (substr($result, 0, 11) == "---\nerror: ") {
            $this->last_error = substr($result, 11);
            return;
        }
        $data = json_decode($result, true);
        if (!is_array($data)) {
            $this->last_error = "Unknown error: $data";
        } elseif (array_key_exists('error', $data)) {
            $this->last_error = $data['error'];
        } else {
            $this->last_error = 'Unknown error';
        }
    }

    private function deserialize_curl_data () {
        if ($this->is_error) return NULL;
        if ($this->curl_content_type == 'message/rfc822') {
            return array('message' => $this->curl_content);
        }

        return json_decode($this->curl_content, true);
    }

} 