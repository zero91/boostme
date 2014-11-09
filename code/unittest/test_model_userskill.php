<?php

require_once(WEB_ROOT . '/code/model/userskill.class.php');

class test_model_userskill {

    public function __construct(& $db) {
        $this->userskill_model = new userskillmodel($db);
    }

    public function test_get_by_uid() {
        $userskill = $this->userskill_model->get_by_uid(16);
        return true;
    }

    public function test_list_by_skill() {
        $userskill = $this->userskill_model->list_by_skill('物理');
        return true;
    }

    public function test_get_list() {
        $skill_list = $this->userskill_model->get_list();
        return true;
    }

    public function test_rownum() {
        $rownum = $this->userskill_model->rownum();
        return true;
    }

    public function test_multi_add() {
        $userskill = $this->userskill_model->get_by_uid(16);
        $new_user_skill = array("物理", "英语");
        $this->userskill_model->multi_add($new_user_skill, 16);
        $userskill = $this->userskill_model->get_by_uid(16);

        return true;
    }

    private $userskill_model;
}

?>
