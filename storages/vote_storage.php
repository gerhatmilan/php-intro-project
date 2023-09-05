<?php
include_once('storage.php');

class VoteStorage extends Storage {
    public function __construct() {
      parent::__construct(new JsonIO('storages/votes.json'));
    }

    public function add($record): string {
      $id = $record["id"];
      if (is_array($record)) {
        $record['id'] = $id;
      }
      else if (is_object($record)) {
        $record->id = $id;
      }
      $this->contents[$id] = $record;
      return $id;
    }

    public function option_voted_by_user($poll, $option, $user) {
      $votes = $this->findAll();

      return in_array($option, $votes[$poll["id"]][$user["id"]]);
    }
}