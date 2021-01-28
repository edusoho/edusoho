<?php

/*
    [UCenter] (C)2001-2099 Comsenz Inc.
    This is NOT a freeware, use is subject to license terms

    $Id: friend.php 1059 2011-03-01 07:25:09Z monkey $
*/

!defined('IN_UC') && exit('Access Denied');

class friendmodel
{
    public $db;
    public $base;

    public function __construct(&$base)
    {
        $this->friendmodel($base);
    }

    public function friendmodel(&$base)
    {
        $this->base = $base;
        $this->db = $base->db;
    }

    public function add($uid, $friendid, $comment = '')
    {
        $direction = $this->db->result_first('SELECT direction FROM '.UC_DBTABLEPRE."friends WHERE uid='$friendid' AND friendid='$uid' LIMIT 1");
        if (1 == $direction) {
            $this->db->query('INSERT INTO '.UC_DBTABLEPRE."friends SET uid='$uid', friendid='$friendid', comment='$comment', direction='3'", 'SILENT');
            $this->db->query('UPDATE '.UC_DBTABLEPRE."friends SET direction='3' WHERE uid='$friendid' AND friendid='$uid'");

            return 1;
        } elseif (2 == $direction) {
            return 1;
        } elseif (3 == $direction) {
            return -1;
        } else {
            $this->db->query('INSERT INTO '.UC_DBTABLEPRE."friends SET uid='$uid', friendid='$friendid', comment='$comment', direction='1'", 'SILENT');

            return $this->db->insert_id();
        }
    }

    public function delete($uid, $friendids)
    {
        $friendids = $this->base->implode($friendids);
        $this->db->query('DELETE FROM '.UC_DBTABLEPRE."friends WHERE uid='$uid' AND friendid IN ($friendids)");
        $affectedrows = $this->db->affected_rows();
        if ($affectedrows > 0) {
            $this->db->query('UPDATE '.UC_DBTABLEPRE."friends SET direction=1 WHERE uid IN ($friendids) AND friendid='$uid' AND direction='3'");
        }

        return $affectedrows;
    }

    public function get_totalnum_by_uid($uid, $direction = 0)
    {
        $sqladd = '';
        if (0 == $direction) {
            $sqladd = "uid='$uid'";
        } elseif (1 == $direction) {
            $sqladd = "uid='$uid' AND direction='1'";
        } elseif (2 == $direction) {
            $sqladd = "friendid='$uid' AND direction='1'";
        } elseif (3 == $direction) {
            $sqladd = "uid='$uid' AND direction='3'";
        }
        $totalnum = $this->db->result_first('SELECT COUNT(*) FROM '.UC_DBTABLEPRE."friends WHERE $sqladd");

        return $totalnum;
    }

    public function get_list($uid, $page, $pagesize, $totalnum, $direction = 0)
    {
        $start = $this->base->page_get_start($page, $pagesize, $totalnum);
        $sqladd = '';
        if (0 == $direction) {
            $sqladd = "f.uid='$uid'";
        } elseif (1 == $direction) {
            $sqladd = "f.uid='$uid' AND f.direction='1'";
        } elseif (2 == $direction) {
            $sqladd = "f.friendid='$uid' AND f.direction='1'";
        } elseif (3 == $direction) {
            $sqladd = "f.uid='$uid' AND f.direction='3'";
        }
        if ($sqladd) {
            $data = $this->db->fetch_all('SELECT f.*, m.username FROM '.UC_DBTABLEPRE.'friends f LEFT JOIN '.UC_DBTABLEPRE."members m ON f.friendid=m.uid WHERE $sqladd LIMIT $start, $pagesize");

            return $data;
        } else {
            return array();
        }
    }

    public function is_friend($uid, $friendids, $direction = 0)
    {
        $friendid_str = implode("', '", $friendids);
        $sqladd = '';
        if (0 == $direction) {
            $sqladd = "uid='$uid'";
        } elseif (1 == $direction) {
            $sqladd = "uid='$uid' AND friendid IN ('$friendid_str') AND direction='1'";
        } elseif (2 == $direction) {
            $sqladd = "friendid='$uid' AND uid IN ('$friendid_str') AND direction='1'";
        } elseif (3 == $direction) {
            $sqladd = "uid='$uid' AND friendid IN ('$friendid_str') AND direction='3'";
        }
        if ($this->db->result_first('SELECT COUNT(*) FROM '.UC_DBTABLEPRE."friends WHERE $sqladd") == count($friendids)) {
            return true;
        } else {
            return false;
        }
    }
}
