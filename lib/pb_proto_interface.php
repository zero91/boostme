<?php
class bm_req_t extends PBMessage
{
  var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
  public function __construct($reader=null)
  {
    parent::__construct($reader);
    $this->fields["1"] = "PBString";
    $this->values["1"] = "";
    $this->fields["2"] = "PBInt";
    $this->values["2"] = "";
    $this->fields["3"] = "PBInt";
    $this->values["3"] = array();
  }
  function name()
  {
    return $this->_get_value("1");
  }
  function set_name($value)
  {
    return $this->_set_value("1", $value);
  }
  function age()
  {
    return $this->_get_value("2");
  }
  function set_age($value)
  {
    return $this->_set_value("2", $value);
  }
  function num($offset)
  {
    $v = $this->_get_arr_value("3", $offset);
    return $v->get_value();
  }
  function append_num($value)
  {
    $v = $this->_add_arr_value("3");
    $v->set_value($value);
  }
  function set_num($index, $value)
  {
    $v = new $this->fields["3"]();
    $v->set_value($value);
    $this->_set_arr_value("3", $index, $v);
  }
  function remove_last_num()
  {
    $this->_remove_last_arr_value("3");
  }
  function num_size()
  {
    return $this->_get_arr_size("3");
  }
}
class bm_res_t extends PBMessage
{
  var $wired_type = PBMessage::WIRED_LENGTH_DELIMITED;
  public function __construct($reader=null)
  {
    parent::__construct($reader);
    $this->fields["1"] = "PBInt";
    $this->values["1"] = "";
  }
  function sum()
  {
    return $this->_get_value("1");
  }
  function set_sum($value)
  {
    return $this->_set_value("1", $value);
  }
}
?>