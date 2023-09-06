# !/bin/bash
if pgrep -x "delete_expired_subscriptions" >/dev/null; then
    echo "The Expired Subscriptions Delete Process Is Running."
else
    echo "The Expired Subscriptions Delete Process Is Not Running. Starting the process"
    bash -c "while true; do cd /home/savior/webapps/behindthemarkets/;wp delete_expired_subscriptions --ps=1 >/dev/null 2>&1; done &" &>/dev/null &
fi