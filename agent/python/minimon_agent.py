import platform
import socket
import time

import psutil
import requests


MINIMON_URL = "http://127.0.0.1:8000/api/agent/report"

AGENT_KEY = "justice@2026"

REPORT_INTERVAL = 60


def get_cpu():
    return psutil.cpu_percent(interval=1)


def get_memory():
    memory = psutil.virtual_memory()

    return {
        "percent": memory.percent,
        "total": memory.total,
        "used": memory.used,
    }


def get_disk():
    disk = psutil.disk_usage("/")

    return {
        "percent": disk.percent,
        "total": disk.total,
        "used": disk.used,
    }


def get_uptime():
    return int(
        time.time() - psutil.boot_time()
    )


def collect():
    memory = get_memory()
    disk = get_disk()

    return {
        "agent_key": AGENT_KEY,

        "hostname": socket.gethostname(),

        "operating_system": (
            f"{platform.system()} "
            f"{platform.release()}"
        ),

        "architecture": platform.machine(),

        "cpu_percent": get_cpu(),

        "memory_percent": memory["percent"],
        "memory_total": memory["total"],
        "memory_used": memory["used"],

        "disk_percent": disk["percent"],
        "disk_total": disk["total"],
        "disk_used": disk["used"],

        "uptime": get_uptime(),
    }


def send_report(data):
    response = requests.post(
        MINIMON_URL,
        json=data,
        timeout=10,
    )

    response.raise_for_status()

    return response.json()


def main():
    print("================================")
    print("       MiniMon Host Agent")
    print("================================")
    print(f"Server: {MINIMON_URL}")
    print(f"Interval: {REPORT_INTERVAL} seconds")
    print()

    while True:

        try:
            data = collect()

            print(
                f"[{time.strftime('%Y-%m-%d %H:%M:%S')}] "
                f"Collecting metrics..."
            )

            print(
                f"CPU: {data['cpu_percent']}% | "
                f"RAM: {data['memory_percent']}% | "
                f"Disk: {data['disk_percent']}%"
            )

            result = send_report(data)

            if result.get("success"):
                print("Report sent successfully.")
            else:
                print("Server rejected the report.")

        except Exception as error:

            print(
                f"Agent error: {error}"
            )

        print(
            f"Next report in "
            f"{REPORT_INTERVAL} seconds."
        )

        print()

        time.sleep(REPORT_INTERVAL)


if __name__ == "__main__":
    main()