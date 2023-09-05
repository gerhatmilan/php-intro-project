<?php
include_once('storage.php');

date_default_timezone_set('Europe/Budapest');

class PollStorage extends Storage {
    public function __construct() {
      parent::__construct(new JsonIO('storages/polls.json'));
    }

    public function add($record) : string {
      $id = count($this->findAll()) + 1;
      if (is_array($record)) {
        $record['id'] = $id;
      }
      else if (is_object($record)) {
        $record->id = $id;
      }
      $this->contents[$id] = $record;

      return $id;
    }

    public function get_active_polls() {
      $polls = $this->findAll();
      $active_polls = [];

      foreach ($polls as $k => $v) {
        if ($v["deadline"] >= date("Y-m-d")) {
            $active_polls[] = $polls[$k];
        }
      }

      return $active_polls;
    }

    public function get_closed_polls() {
      $polls = $this->findAll();
      $closed_polls = [];

      foreach ($polls as $k => $v) {
        if ($v["deadline"] < date("Y-m-d")) {
            $closed_polls[] = $polls[$k];
        }
      }

      return $closed_polls;
    }

    public function user_voted($poll, $user) : bool {
      $polls = $this->findAll();
      if (in_array($user["id"], $polls[$poll["id"]]["voted"])) {
        return true;
      }

      return false;
    }
}