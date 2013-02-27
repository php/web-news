<?php
/*
  +----------------------------------------------------------------------+
  | PHP.net Website Systems                                              |
  +----------------------------------------------------------------------+
  | Copyright (c) 2011 The PHP Group                                     |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author:                                                              |
  |	Kalle Sommer Nielsen <kalle@php.net>                             |
  +----------------------------------------------------------------------+
*/

$translation = Array(

/* Common */
'title'                          => 'PHP News', 
'encoding'                       => 'utf-8',

'success'                        => 'Success', 
'error'                          => 'Error', 
'subscribe'                      => 'Subscribe', 
'unsubscribe'                    => 'Unsubscribe', 
'attachments'                    => 'Attachments', 
'references'                     => 'References', 
'reference_x'                    => 'Reference #{1}', 
'redirect'                       => 'Redirect', 
'home'                           => 'Home', 
'news_groups'                    => 'News groups', 
'preferences'                    => 'Preferences', 
'mailing_lists'                  => 'Mailing lists', 
'subject'                        => 'Subject', 
'author'                         => 'Author', 
'date'                           => 'Date', 
'lines'                          => 'Lines', 
'by'                             => 'by', 
'email'                          => 'Email', 
'next'                           => 'Next', 
'previous'                       => 'Previous', 
'showing_x_y_of_z'               => 'Showing {1} - {2} of {3}', 
'copyright_x'                    => 'Copyright &copy; 2001-{1} The PHP Group', 
'my_phpdotnet'                   => 'My PHP.net', 
'contact'                        => 'Contact', 
'other_phpdotnet_sites'          => 'Other PHP.net sites', 
'mirror_sites'                   => 'Mirror sites', 
'privacy_policy'                 => 'Privacy policy', 

/* Languages */
'lang_en'                        => 'English', 
'lang_da'                        => 'Danish / Dansk', 

/* Popular (and quick) groups */
'popular_group_core'             => 'PHP at the Core', 
'popular_group_documentation'    => 'PHP Documentation', 
'popular_group_translations'     => 'Documentation translations', 

/* NNTP */
'nntp_error_connect'             => 'Failed to connect to news server', 
'nntp_error_handshake'           => 'Failed to recieve handshake from news server', 
'nntp_error_command'             => 'Unable to send command to server; command is too long', 

/* posttohost() */
'posttohost_error_parse'         => 'Failed to parse host URL', 
'posttohost_error_connect_x'     => 'Failed to connect to host: {1}', 

/* Index */
'index_error_list'               => 'Failed to get the list over news groups', 
'index_all_news_groups'          => 'All news groups', 
'index_welcome'                  => 'Welcome!', 
'index_paragraph_1'              => 'This is a web interface to the PHP mailing lists as reflected on the <a href="news://<?php echo NNTP_SERVER; ?>/"><?php echo NNTP_SERVER; ?> NNTP server</a>. It is open source and can be found at the PHP <a href="http://svn.php.net/repository/web/php-news/trunk">SVN repository</a>.', 
'index_paragraph_2'              => 'The news server software that is used is <a href="http://trainedmonkey.com/colobus/">colobus</a>.', 

/* Subscribe */
'subscribe_request_subscribe'    => 'subscribe', 
'subscribe_request_unsubscribe'  => 'unsubscribe', 
'subscribe_error_email'          => 'Invalid email address supplied', 
'subscribe_error_ml_data'        => 'Failed to get mailing list data', 
'subscribe_error_x_problem'      => 'Failed to {1} you due to technical problems', 
'subscribe_success_subscribe'    => 'An email have been sent to you to confirm your subscription to this list', 
'subscribe_success_unsubscribe'  => 'An email have been sent to you to confirm your unsubscription from this list', 

/* Groups */
'groups_subscription_options'    => 'Subscription options', 
'groups_subscription_info'       => 'You can subscribe to this news group to get messages sent directly to your inbox.', 
'groups_feeds_newsgroup_at'      => 'newsgroup at', 
'groups_error'                   => 'Failed to get information about the specified group', 
'groups_error_xover'             => 'Failed to get XOVER data', 

/* Attachments */
'attachment_default_filename'    => 'attachment.txt', 
'attachment_error'               => 'Failed to get information about the specified attachment', 

/* Articles */
'article_error'                  => 'Failed to get information about the specified article', 
'article_unnamed_attachment_x'   => 'Unnamed attachment #{1}', 

/* Preferences */
'prefs_paragraph_1'              => 'Preferences are configurable parts of the news server web interface.', 
'prefs_paragraph_2'              => 'Notice that for preferences to work, cookies in your browser must be enabled.', 
'prefs_save'                     => 'Save preferences', 
'prefs_saved'                    => 'Saved preferences', 
'prefs_language'                 => 'Interface language', 
'prefs_language_paragraph'       => 'The basic interface is available in multiple translations. Note that messages are not translated.', 
'prefs_miniflags'                => 'Display mini flags', 
'prefs_miniflags_paragraph'      => 'If enabled, mini flags specific to non-english groups are shown.', 
'prefs_display_popular_groups'   => 'Display popular news groups', 
'prefs_groups_paragraph'         => 'If enabled, active news groups are shown at the top of the index and message list pages.', 
'prefs_disabled'                 => 'Disabled', 
'prefs_enabled'                  => 'Enabled', 
'prefs_enabled_index_only'       => 'Enabled, but only on the index', 
'prefs_email_paragraph'          => 'You can enter your email below to have it filled in the subscription / unsubscription forms on message list pages.', 
);

?>