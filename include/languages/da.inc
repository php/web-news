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
'title'                          => 'PHP Nyheder', 
'encoding'                       => 'iso-8859-1',

'success'                        => 'Succes', 
'error'                          => 'Fejl', 
'subscribe'                      => 'Tilmeld', 
'unsubscribe'                    => 'Afmeld', 
'attachments'                    => 'Vedhæftede filer', 
'references'                     => 'Referencer', 
'reference_x'                    => 'Reference #{1}', 
'redirect'                       => 'Vidrestil', 
'home'                           => 'Hjem', 
'news_groups'                    => 'Nyhedsgrupper', 
'preferences'                    => 'Præferencer', 
'mailing_lists'                  => 'Postlister', 
'subject'                        => 'Emne', 
'author'                         => 'Forfatter', 
'date'                           => 'Dato', 
'lines'                          => 'Linjer', 
'by'                             => 'af', 
'email'                          => 'E-mail', 
'next'                           => 'Næste', 
'previous'                       => 'Forrige', 
'showing_x_y_of_z'               => 'Viser {1} - {2} af {3}', 
'copyright_x'                    => 'Copyright &copy; 2001-{1} The PHP Group', 
'my_phpdotnet'                   => 'Min PHP.net', 
'contact'                        => 'Kontakt', 
'other_phpdotnet_sites'          => 'Andre PHP.net sider', 
'mirror_sites'                   => 'Mirror sider', 
'privacy_policy'                 => 'Privatlivspolitik', 

/* Languages */
'lang_en'                        => 'Engelsk / English', 
'lang_da'                        => 'Dansk', 

/* Popular (and quick) groups */
'popular_group_core'             => 'PHP i kernen', 
'popular_group_documentation'    => 'PHP Dokumentationen', 
'popular_group_translations'     => 'Dokumentations oversættelser', 

/* NNTP */
'nntp_error_connect'             => 'Kan ikke forbinde til nyhedsserver', 
'nntp_error_handshake'           => 'Kan ikke modtage håndtryk fra nyhedsserver', 
'nntp_error_command'             => 'Kan ikke sende kommando til server, kommandoen er for lang', 

/* posttohost() */
'posttohost_error_parse'         => 'Kan ikke forstå vært URL', 
'posttohost_error_connect_x'     => 'Kan ikke forbinde til vært: {1}', 

/* Index */
'index_error_list'               => 'Kan ikke indsamle listen over nyhedsgrupper', 
'index_all_news_groups'          => 'Alle nyhedsgrupper', 
'index_welcome'                  => 'Velkommen!', 
'index_paragraph_1'              => 'Dette er et web-interface til PHP\'s postlister, som afspejles på <a href="news://<?php echo NNTP_SERVER; ?>/"><?php echo NNTP_SERVER; ?> NNTP serveren</a>. Det er open-source og kan findes i PHP\'s <a href="http://svn.php.net/repository/web/php-news/trunk">SVN repository</a>.', 
'index_paragraph_2'              => 'Nyhedsserver softwaren, der anvendes, er <a href="http://trainedmonkey.com/colobus/">colobus</a>.', 

/* Subscribe */
'subscribe_request_subscribe'    => 'tilmelde', 
'subscribe_request_unsubscribe'  => 'framelde', 
'subscribe_error_email'          => 'Ugyldig e-mail adresse', 
'subscribe_error_ml_data'        => 'Kan ikke indsamle postliste data', 
'subscribe_error_x_problem'      => 'Kan ikke {1} dig på grund af tekniske problemer', 
'subscribe_success_subscribe'    => 'En e-mail er blevet sendt til dig for at bekræfte dit abonnement på denne liste', 
'subscribe_success_unsubscribe'  => 'En e-mail er blevet sendt til dig for at bekræfte din afmelding fra denne liste', 

/* Groups */
'groups_subscription_options'    => 'Abonnement', 
'groups_subscription_info'       => 'Du kan abonnere på denne nyhedgruppe for at få meddelelser sendt direkte til din indbakke.', 
'groups_feeds_newsgroup_at'      => 'nyhedsgruppe på', 
'groups_error'                   => 'Kan ikke indsamle oplysninger om gruppen', 
'groups_error_xover'             => 'Kan ikke indsamle XOVER data', 

/* Attachments */
'attachment_default_filename'    => 'fil.txt', 
'attachment_error'               => 'Kan ikke indsamle oplysninger om den vedhæftede fil', 

/* Articles */
'article_error'                  => 'Kan ikke indsamle oplysninger om artiklen', 
'article_unnamed_attachment_x'   => 'Unnavngivet fil #{1}', 

/* Preferences */
'prefs_paragraph_1'              => 'Præferencer er konfigurerbare dele af nyhedsserverens web-interface.', 
'prefs_paragraph_2'              => 'Bemærk, at for få præferencer til at virke, skal cookies være slået til i din browser.', 
'prefs_save'                     => 'Gem præferencer', 
'prefs_saved'                    => 'Præferencer gemt', 
'prefs_language'                 => 'Sprog', 
'prefs_language_paragraph'       => 'Den grundlæggende grænseflade er tilgængelige i flere oversættelser. Bemærk at meddelelser ikke er oversat.', 
'prefs_miniflags'                => 'Mini flag', 
'prefs_miniflags_paragraph'      => 'Hvis aktiveret, vises mini flag ud for grupper hvilket sprog er andet end engelsk.', 
'prefs_display_popular_groups'   => 'Populære nyhedsgrupper', 
'prefs_groups_paragraph'         => 'Hvis aktiveret, vises aktive nyhedsgrupper i toppen af siden.', 
'prefs_disabled'                 => 'Deaktiveret', 
'prefs_enabled'                  => 'Aktiveret', 
'prefs_enabled_index_only'       => 'Aktiveret, men kun på forsiden', 
'prefs_email_paragraph'          => 'Du kan indtaste din email nedenfor for at få udfyldt tilmelding / afmelding skemaer på meddelelseslisterne.', 
);

?>