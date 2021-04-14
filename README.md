## About ReClique Schedules Sync

This is the Group Exercises import for [ReClique](https://reclique.com/).

###How to install:
1. Download and enable this module
2. Go to /admin/openy/integrations/reclique-gxp and fill the settings
3. Configure cron or run next command once `drush mim --group=reclique_gxp_import`
   this will run full migration of Activities, Classes and Sessions.
   Please note, locations must be created before, otherwise sessions won't be created at all.
