<?php

class responseBean implements JsonSerializable {

    protected $success;
    protected $message;
    protected $body = '""'; 

    public function setSuccess($success) {
        $this->success = $success;
    }
    public function getSuccess() {
        return $this->success;
    }	

    public function setMessage($message) {
        $this->message = $message;
    }
	public function getMessage() {
        return $this->message;
    }

    public function setBody($body) {
        $this->body = $body;
    }
	public function getBody() {
        return $this->body;
    }

    public function jsonSerialize() {
		
        return get_object_vars($this);
    }
}
