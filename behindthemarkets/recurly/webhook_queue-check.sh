# !/bin/bash
if pgrep -f "webhook_queue_process" >/dev/null; then
    echo "The Webhook process is running."
else
    echo "The Folloup process is not running. Starting the process"
    bash -c "while true; do cd /home/savior/webapps/behindthemarkets/;wp webhook_queue_process >/dev/null 2>&1; done &" &>/dev/null &
fi
