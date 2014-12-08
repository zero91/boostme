<?php

class cache { 
    public function __construct(&$db) {
        $this->db = $db;
    }

    // 加载数据，如缓存中无此数据，则从数据库中查出相应数据，并做缓存
    public function load($cachename, $key='', $val='', $orderby='') {
        $arraydata = $this->read($cachename);
        if (!$arraydata) {
            $sql = "SELECT * FROM $cachename";
            $orderby && $sql .= " ORDER BY $orderby ASC";

            $arraydata = $this->db->fetch_all($sql, $key, $val);
            $this->write($cachename, $arraydata);
        }
        return $arraydata;
    }

    // 读取缓存文件，若缓存文件不存在或无效，则返回false
    public function read($cachename, $cachetime=0) {
        $cachefile = $this->getfile($cachename);
        if ($this->isvalid($cachename, $cachetime)) {
            return @include $cachefile;
        }
        return false;
    }

    public function write($cachename, $arraydata) {
        $cachefile = $this->getfile($cachename);
        if (!is_array($arraydata)) {
            return false;
        }

        $strdata = "<?php\nreturn " . var_export($arraydata, true) . ";\n?>";
        $bytes = writetofile($cachefile, $strdata);
        return $bytes;
    }

    public function remove($cachename) {
        $cachefile = $this->getfile($cachename);
        if (file_exists($cachefile)) {
            unlink($cachefile);
        }
    }

    // 根据缓存数据名称，获取缓存文件详细路径
    private function getfile($cachename) {
        return WEB_ROOT . '/private/cache/' . $cachename . '.php';
    }

    // 判断缓存文件是否有效
    private function isvalid($cachename, $cachetime) {
        if ($cachetime == 0) {
            return true;
        }

        $cachefile = $this->getfile($cachename);
        if (!is_readable($cachefile) || $cachetime < 0) {
            return false;
        }

        clearstatcache();
        return (time() - filemtime($cachefile)) < $cachetime;
    }

    private $db;
}

?>
