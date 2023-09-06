# !/bin/bash
if pgrep -x "active_subscriptions" >/dev/null; then
    echo "The Check Active Subscriptions Process Is Running."
else
    echo "The Check Active Subscriptions Process Is Not Running. Starting the process"
    bash -c "while true; do cd /home/savior/webapps/behindthemarkets/;wp active_subscriptions >/dev/null 2>&1; done &" &>/dev/null &
fi
