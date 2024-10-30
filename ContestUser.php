<?php

/**
 * @file ContestUser.php
 * Contains \contest\ContestUser.
 */
namespace contest;

/**
 * An easy to access contest user.
 */
class ContestUser {
  public $uid;
  public $name;
  public $email;
  public $status;
  public $full_name;
  public $name_f;
  public $name_l;
  public $business;
  public $address;
  public $city;
  public $state;
  public $zip;
  public $phone;
  public $birthdate;
  public $optin;
  public $title;
  public $url;

  /**
   * Just make sure there are some defaults that won't throw an error.
   *
   * @param $uid (int) The user's ID.
   *
   * @return (ContestUser) A ContestUser object.
   */
  public function __construct($uid = 0) {
    $usr = (!empty($uid) && is_numeric($uid))? get_user_by('id', $uid): NULL;
    $profile = (!empty($uid) && is_numeric($uid))? get_user_meta($uid): NULL;

    $this->uid = !empty($usr->ID)? $usr->ID: 0;
    $this->name = !empty($usr->user_login)? esc_html($usr->user_login): '';
    $this->email = !empty($usr->user_email)? esc_html($usr->user_email): '';
    $this->status = !empty($usr->ID)? 1: 0;
    $this->full_name = trim((!empty($usr->first_name)? esc_html($usr->first_name): '') . ' ' . (!empty($usr->last_name)? esc_html($usr->last_name): ''));
    $this->name_f = !empty($usr->first_name)? esc_html($usr->first_name): '';
    $this->name_l = !empty($usr->last_name)? esc_html($usr->last_name): '';
    $this->address = !empty($profile['contest_address'][0])? esc_html($profile['contest_address'][0]): '';
    $this->city = !empty($profile['contest_city'][0])? esc_html($profile['contest_city'][0]): '';
    $this->state = !empty($profile['contest_state'][0])? esc_html($profile['contest_state'][0]): '';
    $this->zip = !empty($profile['contest_zip'][0])? esc_html($profile['contest_zip'][0]): '';
    $this->phone = !empty($profile['contest_phone'][0])? esc_html(esc_html($profile['contest_phone'][0])): '';
    $this->birthdate = !empty($profile['contest_birthdate'][0])? $profile['contest_birthdate'][0]: '';
    $this->optin = !empty($profile['contest_optin'][0])? $profile['contest_optin'][0]: '';
    $this->url = !empty($usr->user_url)? esc_url($usr->user_url): '';

    if (!empty($usr->display_name)) {
      $this->title = esc_html($usr->display_name);
    }
    elseif (!empty($this->full_name)) {
      $this->title = esc_html($this->full_name);
    }
    elseif (!empty($usr->user_login)) {
      $this->title = esc_html($usr->user_login);
    }
    else {
      $this->title = '';
    }
    $this->business = $this->title;
  }
  /**
   * Magic get.
   * Since the properties are public I don't actually think I need this.
   */
  public function __get($property) {
    return property_exists($this, $property)? $this->{$property}: NULL;
  }
  /**
   * Magic set.
   * This will actually prevent new properties from being created.
   */
  public function __set($property, $value) {
    return property_exists($this, $property)? $this->{$property} = $value: NULL;
  }
  /**
   * Create a contest user.
   *
   * @return (bool) True if successful, otherwise false.
   */
  public function create() {
    $password = $this->passGen();
    $uid = wp_create_user($this->name, $password, $this->email);

    if (!is_numeric($uid)) {
      return FALSE;
    }
    $this->uid = $uid;

    $args = array(
      'ID'         => $uid,
      'first_name' => $this->name_f,
      'last_name'  => $this->name_l,
    );
    wp_update_user($args);

    add_user_meta($uid, 'contest_address', $this->address, TRUE);
    add_user_meta($uid, 'contest_city', $this->city, TRUE);
    add_user_meta($uid, 'contest_birthdate', $this->birthdate, TRUE);
    add_user_meta($uid, 'contest_phone', $this->phone, TRUE);
    add_user_meta($uid, 'contest_state', $this->state, TRUE);
    add_user_meta($uid, 'contest_zip', $this->zip, TRUE);
    add_user_meta($uid, 'contest_optin', $this->optin, TRUE);
    
    wp_new_user_notification($this->uid, NULL, 'both');
    
    $msg = "You have been added to the %s website. Below is your login information.<br />\nUsername: %s.<br />\nEmail: %s.<br />\nAn account activation email has been sent to the provided address. If you have a problem logging in, use the password recovery tool located at the top of the user's login page.";

    contest_set_msg(sprintf(__($msg), bloginfo('name'), $this->name, $this->email));

    return $uid;
  }
  /**
   * Determine if the user has a complete profile.
   *
   * @param $role (string) The type of contest user, ("host", "sponsor", "").
   *
   * @return (bool) True of the profile is complete, otherwise FALSE.
   */
  function profileComplete() {
    if (!$this->validField('address', $this->address)) {
      return FALSE;
    }
    if (!$this->validField('city', $this->city)) {
      return FALSE;
    }
    if (!$this->validField('dob', $this->birthdate)) {
      return FALSE;
    }
    if (!$this->validField('name', $this->name)) {
      return FALSE;
    }
    if (!$this->validField('phone', $this->phone)) {
      return FALSE;
    }
    if (!$this->validField('state', $this->state)) {
      return FALSE;
    }
    if (!$this->validField('zip', $this->zip)) {
      return FALSE;
    }
    return TRUE;
  }
  /**
   * Create or update a user from POST fields.
   *
   * @param $post (array) An array of user fields, (probably from $_POST and/or $_GET).
   *
   * @return (int|bool) The user ID on success, false on failure.
   */
  public function savePost($post = array()) {
    global $wpdb;
    
    $this->email = !empty($post['user_email'])? sanitize_email($post['user_email']): '';
    $this->name_f = !empty($post['first_name'])? sanitize_text_field($post['first_name']): '';
    $this->name_l = !empty($post['last_name'])? sanitize_text_field($post['last_name']): '';
    $this->address = !empty($post['contest_address'])? sanitize_text_field($post['contest_address']): '';
    $this->city = !empty($post['contest_city'])? sanitize_text_field($post['contest_city']): '';
    $this->state = !empty($post['contest_state'])? sanitize_text_field($post['contest_state']): '';
    $this->zip = !empty($post['contest_zip'])? sanitize_text_field($post['contest_zip']): '';
    $this->phone = !empty($post['contest_phone'])? sanitize_text_field($post['contest_phone']): '';
    $this->optin = !empty($post['contest_optin'])? (int) $post['contest_optin']: '';
    $this->birthdate = (!empty($post['birthdate']) && count($post['birthdate']) == 3)? mktime(0, 0, 0, (int) $post['birthdate']['month'], (int) $post['birthdate']['day'], (int) $post['birthdate']['year']): '';

    if ($this->uid) {
      return $this->update();
    }
    if (email_exists($this->email)) {
      return $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->prefix}users WHERE user_email = %s", $this->email));
    }
    $this->nameGen($this->email);

    return $this->create();
  }
  /**
   * Return a single CSV line for a user.
   *
   * @param $properties (array) An array of property names to export.
   *
   * @return $csv (string) A comma seperated list of users.
   */
  public function toCsv($properties = array()) {
    $csv = '';

    foreach ($properties as $property) {
      if (property_exists($this, $property)) {
        $csv .= ($csv? ',"': '"') . str_replace('"', '\"', $this->{$property}) . '"';
      }
      else {
        $csv .= $csv? ',""': '""';
      }
    }
    return $csv? "$csv\n": '';
  }
  /**
   * Update a contest user.
   *
   * @return (bool) True if successful, otherwise false.
   */
  public function update() {
    $args = array(
      'ID'         => $this->uid,
      'first_name' => $this->name_f,
      'last_name'  => $this->name_l,
    );
    wp_update_user($args);

    update_user_meta($this->uid, 'contest_address', $this->address);
    update_user_meta($this->uid, 'contest_city', $this->city);
    update_user_meta($this->uid, 'contest_birthdate', $this->birthdate);
    update_user_meta($this->uid, 'contest_phone', $this->phone);
    update_user_meta($this->uid, 'contest_state', $this->state);
    update_user_meta($this->uid, 'contest_zip', $this->zip);
    update_user_meta($this->uid, 'contest_optin', $this->optin);

    return $this->uid;
  }
  /**
   * Generate a username from an email address.
   *
   * @param $name (string) A initial user name.
   *
   * @return (string) A username that hopefully isn't too terrible.
   */
  protected function nameGen($name = '') {
    $min = 10;
    $max = 99;
    $name = preg_replace('/@.*$/', '', $name);
    $username = $name;
    
    for ($i = $min; $i <= $max; $i++) {
      $found = username_exists($username);

      if (!$found) {
        $this->name = $username;
        return $username;
      }
      $username = $this->strokit($name) . '-' . rand($min, $max);
    }
    return $this->nameGen($this->strokit("$this->name_f $this->name_l " . rand($min, $max)));
  }
  /**
   * Generate a password from an email address.
   *
   * @param $email (string) The user's email address.
   *
   * @return (string) A password that hopefully isn't too terrible.
   */
  protected function passGen() {
    return preg_replace('/@.*/', '', strtolower($this->email)) . '-' . substr(md5(CONTEST_TIME . rand(0, 100)), 0, rand(4, 6));
  }
  /**
   * Convert the provided string to a lowercase stroke delimited string, (uppercase converted to lower, consecutive non alpha-numeric characters converted to a stroke).
   *
   * @param $txt (string) The string to convert.
   *
   * @return (string) A lowercase stroke delimited string.
   */
  protected function strokit($txt) {
    return preg_replace(array('/[^a-z0-9]+/', '/^-+|-+$/'), array('-', ''), strtolower($txt));
  }
  /**
   * Field validation.
   *
   * @param $type (string) The type of validation tests to run on the field.
   * @param $value (int|string) The value fo the field we're validating.
   *
   * @return (bool) True if the field is valid.
   */
  protected function validField($type, $value) {
    if (empty($type) || !isset($value)) {
      return FALSE;
    }
    switch ($type) {
      case 'address':
        return preg_match('/[a-zA-Z]{2,' . CONTEST_ADDRESS_MAX . '}/', $value)? TRUE: FALSE;

      case 'city':
        return preg_match('/[a-zA-Z\s-.]{2,' . CONTEST_CITY_MAX . '}/', $value)? TRUE: FALSE;

      case 'dob':
        return is_numeric($value)? TRUE: FALSE;

      case 'email':
        return (strlen($value) <= CONTEST_EMAIL_MAX && TRUE)? TRUE: FALSE;

      case 'email_dupe':
        return email_exists($value);

      case 'filesystem':
        return (preg_match('/^\w+$/', trim($value)) && strlen(trim($value)) <= CONTEST_STRING_MAX)? TRUE: FALSE;

      case 'int':
        return (is_numeric($value) && intval($value) > 0 && intval($value) <= CONTEST_INT_MAX)? TRUE: FALSE;

      case 'name':
        return preg_match('/[a-zA-Z]{1,' . CONTEST_NAME_MAX . '}/', $value)? TRUE: FALSE;

      case 'phone':
        return (strlen(preg_replace('/\D+/', '', $value)) >= 10 && strlen($value) < CONTEST_PHONE_MAX)? TRUE: FALSE;

      case 'state':
        $states = _contest_get_states('US');
        if (!empty($states)) {
          return !empty($states[$value])? TRUE: FALSE;
        }
        else {
          return (preg_match('/[a-zA-z\s\'\-\.]+/', $value) && strlen(trim($value)) <= CONTEST_STATE_MAX)? TRUE: FALSE;
        }
      case 'string':
        return (preg_match('/[a-zA-z]+/', $value) && strlen(trim($value)) <= CONTEST_STRING_MAX)? TRUE: FALSE;

      case 'username':
        return preg_match('/\w{2,' . USERNAME_MAX_LENGTH . '}/', $value)? TRUE: FALSE;

      case 'username_dupe':
        return username_exists($value);

      case 'zip':
        return (strlen(preg_replace('/\D+/', '', $value)) == CONTEST_ZIP_MAX)? TRUE: FALSE;
    }
    return FALSE;
  }
}
