<?php

class URLBean implements JsonSerializable {

    protected $url;

    public function __construct($url) {
        $this->url = $url;
    }
	
	public function getURL() {
        return $this->url;
    }
	public function setURL($url) {
        $this->url = $url;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }
}
