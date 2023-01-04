<?php

class userBean implements JsonSerializable {

    protected $pk_user;
    protected $fk_user_role;
    protected $email;
    protected $auth;

    public function __construct($pk_user, $fk_user_role, $email, $token) {
        $this->pk_user = $pk_user;
        $this->fk_user_role = $fk_user_role;
        $this->email = $email;
        $this->token = $token;
    }
	
	public function get_pk_user() {
        return $this->pk_user;
    }
	public function set_pk_user($pk_user) {
        $this->pk_user = $pk_user;
    }

    public function get_fk_user_role() {
        return $this->fk_user_role;
    }
    public function set_fk_user_role($fk_user_role) {
        $this->fk_user_role = $fk_user_role;
    }

    public function get_email() {
        return $this->email;
    }
    public function set_email($email) {
        $this->email = $email;
    }

    public function get_token() {
        return $this->token;
    }
    public function set_token($token) {
        $this->token = $token;
    }    

    public function jsonSerialize() {
        return get_object_vars($this);
    }
}
