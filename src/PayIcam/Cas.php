<?php

include('class/httpful.phar');

namespace PayIcam;

class Cas{
    protected $url;
    protected $timeout;

    public function __construct($url, $timeout=10) {
        $this->url = $url;
        $this->timeout = $timeout;
    }

    public function authenticate($ticket, $service) {
        $r = \Httpful\Request::get($this->getValidateUrl($ticket, $service))
          ->sendsXml()
          ->timeoutIn($this->timeout)
          ->send();
        $r->body = str_replace("\n", "", $r->body);
        try {
            $xml = new SimpleXMLElement($r->body);
        }catch (Exception $e) {
            echo "Return cannot be parsed : '{$r->body}'",  $e->getMessage(), "\n";
            // return (string)"Return cannot be parsed";
        }

        $namespaces = $xml->getNamespaces();

        $serviceResponse = $xml->children($namespaces['cas']);
        $user = $serviceResponse->authenticationSuccess->user;

        if ($user) {
            return (string)$user; // cast simplexmlelement to string
        }
        else {
            $authFailed = $serviceResponse->authenticationFailure;
            if ($authFailed) {
                $attributes = $authFailed->attributes();
                echo ("AuthenticationFailure : ".$attributes['code']." ($ticket, $service)");
                // return (string)"AuthenticationFailure";
            }
            else {
                echo ("Cas return is weird : '{$r->body}'");
                // return (string)"Cas return is weird";
            }
        }
        // never reach there
    }

    public function logout() {
        $r = \Httpful\Request::get($this->url."logout")
          ->sendsXml()
          ->timeoutIn($this->timeout)
          ->send();
        $r->body = str_replace("\n", "", $r->body);
        try {
            $xml = new SimpleXMLElement($r->body);
            return true;
        }catch (Exception $e) {
            return false;
        }
    }

    public function getValidateUrl($ticket, $service) {
        return $this->url."serviceValidate?ticket=".urlencode($ticket)."&service=".urlencode($service);
    }
}