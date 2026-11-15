# VPS MySQL Watchdog

Artifacts to install an automatic MySQL watchdog on the VPS.

Files:
- `mysql-backend-watchdog.sh`: checks `mysql.service`, verifies `mysqladmin ping`, and restarts MySQL if needed.
- `mysql-backend-watchdog.service`: oneshot `systemd` service that runs the watchdog script.
- `mysql-backend-watchdog.timer`: runs the service every minute after boot.

Target paths on the VPS:
- `/usr/local/sbin/mysql-backend-watchdog.sh`
- `/etc/systemd/system/mysql-backend-watchdog.service`
- `/etc/systemd/system/mysql-backend-watchdog.timer`

Validation:
- `systemctl list-timers mysql-backend-watchdog.timer`
- `journalctl -u mysql-backend-watchdog.service -n 20 --no-pager`
