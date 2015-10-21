<?php

class UserStates {

  const WAITING = 0;    // Waiting for user email confirmation, admin account activation and such
  const ACTIVE  = 1;    // Active User
  const LOCKED  = 2;    // Temporarilly Locked user
  const BANNED  = 3;    // Permanent locked user

}

?>