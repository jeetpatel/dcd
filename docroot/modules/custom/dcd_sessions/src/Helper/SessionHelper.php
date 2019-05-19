<?php

namespace Drupal\dcd_sessions\Helper;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\node\Entity\Node;

/**
 * SessionHelper.
 */
class SessionHelper {

  private $userData = FALSE;
  private $userID = FALSE;
  const SESSION_SUBMITED_TEMPLATE = 'sessions_submitted';
  const SESSION_EMAIL_KEY = 'session_submit';

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The current user.
   *
   * @var Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;
  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManager
   */
  private $mailManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    LoggerChannelFactoryInterface $logger,
    AccountInterface $current_user,
    MailManagerInterface $mailManager) {
    $this->logger = $logger;
    $this->currentUser = $current_user;
    $this->mailManager = $mailManager;
  }

  /**
   * Get current logged user.
   *
   * @return object
   *   User object if logged, FALSE.
   */
  public function getCurrentUser() {
    if ($this->currentUser->id()) {
      return $this->currentUser;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Check user is authenticated or not.
   *
   * @param int $userID
   *   User id.
   *
   * @return bool
   *   Return true or false.
   */
  public function isAuthincatedUser($userID = NULL) {
    $user = $this->userLoad($userID);
    $roles = $user->getRoles();
    if ((in_array('authenticated', $roles))
        || (in_array('administrator', $roles))) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Return current user id.
   *
   * @return int
   *   Return user id.
   */
  public function getUserId() {
    return $this->currentUser->id();
  }

  /**
   * Load current user or Other user by user id.
   *
   * @param int $userID
   *   User id.
   *
   * @return object
   *   User object.
   */
  public function userLoad($userID = NULL) {
    if (empty($userID)) {
      $userID = $this->getUserId();
    }
    // Cache user details in variable.
    if ((empty($this->userData)) || ($this->userID != $userID)) {
      $this->userData = User::load($userID);
    }
    $this->userID = $userID;
    return $this->userData;
  }

  /**
   * Get email template by URL alias.
   *
   * @param string $key
   *   Email Template URL alias.
   *
   * @return bool
   *   Return email template entity, FALSE.
   */
  public function getEmailTemplate($key) {
    // Load template by URL alias.
    $path = \Drupal::service('path.alias_manager')->getPathByAlias('/' . $key);
    if (preg_match('/node\/(\d+)/', $path, $matches)) {
      return Node::load($matches[1]);
    }
    return FALSE;
  }

  /**
   * Send Email to session submitted user.
   *
   * @param object $entity
   *   Entity object.
   */
  public function sendSessionSubmitedEmail($entity) {
    $template = $this->getEmailTemplate(self::SESSION_SUBMITED_TEMPLATE);
    if ($template) {
      $token = \Drupal::token();
      $body = $token->replace($template->body->value, [
        'node' => $entity,
        'user' => $this->currentUser,
      ]);
      $subject = $token->replace($template->field_subject->value, [
        'node' => $entity,
      ]);
      $this->sendMail(self::SESSION_EMAIL_KEY, $this->currentUser->getEmail(), $subject, $body);
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Send mail.
   *
   * @param string $key
   *   The key of mail.
   * @param string $to
   *   Email id to send email.
   * @param string $subject
   *   The subject for the mail.
   * @param string $body
   *   The body for the mail.
   */
  public function sendMail($key, $to, $subject, $body) {
    try {
      // Getting current user langcode.
      $langcode = 'en';
      if (!empty($this->currentUser)) {
        $langcode = $this->currentUser->getPreferredLangcode();
      }
      $data = [
        'body' => $body,
        'subject' => $subject,
      ];
      $this->mailManager->mail('dcd_sessions', $key, $to, $langcode, $data, NULL, TRUE);
      return TRUE;
    }
    catch (\Exception $e) {
      $this->writeLog(__FUNCTION__, $e->getMessage(), $e->getCode(), 'error');
      return FALSE;
    }
  }

  /**
   * Write log message.
   *
   * @param string $key
   *   Message key.
   * @param string $message
   *   Message to write.
   * @param string $code
   *   Message code to write.
   * @param string $type
   *   Log type.
   */
  public function writeLog($key, $message, $code, $type = 'notice') {
    switch ($type) {
      case 'notice':
        $this->logger->get($key)->notice($message . ', Code:' . $code);
        break;

      case 'error':
        $this->logger->get($key)->error($message . ', Code:' . $code);
        break;

      case 'warning':
        $this->logger->get($key)->warning($message . ', Code:' . $code);
        break;

      case 'critical':
        $this->logger->get($key)->critical($message . ', Code:' . $code);
        break;

      case 'info':
        $this->logger->get($key)->info($message . ', Code:' . $code);
        break;

      default:
        $this->logger->get($key)->alert($message . ', Code:' . $code);
        break;
    }
  }

}
