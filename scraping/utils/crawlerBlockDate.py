import requests
import sys
import http.client
import time
http.client._MAXHEADERS = 1000

from requests.adapters import HTTPAdapter
from requests.packages.urllib3.util.retry import Retry

def get_archived_snapshots(site):
    # retrieve all timestamps
    cdx_url = f"http://web.archive.org/cdx/search/cdx?url={site}/robots.txt&output=json&fl=timestamp"
    headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
}
    session = requests.Session()
    retry = Retry(total=5, backoff_factor=0.1, status_forcelist=[ 500, 502, 503, 504 ])
    adapter = HTTPAdapter(max_retries=retry)
    session.mount('http://', adapter)
    session.mount('https://', adapter)

    response = requests.get(cdx_url, headers=headers)
    response.raise_for_status()
    data = response.json()
    time.sleep(14);
    return [entry[0] for entry in data[1:]]


def user_agent_present_in_snapshot(timestamp, user_agent, site):
    url = f"https://web.archive.org/web/{timestamp}/http://{site}/robots.txt"
    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    }
    response = requests.get(url, headers=headers)
    time.sleep(14);
    return user_agent in response.text


def adaptive_binary_search(user_agent, site):
    timestamps = get_archived_snapshots(site)

    if not timestamps:
        # print("No archives found for the specified site.")
        return "Unretrievable"

    start, end = 0, len(timestamps) - 1

    # binary search until we reach 10 timestamps remaining
    while end - start > 10:
        mid = (start + end) // 2
        if user_agent_present_in_snapshot(timestamps[mid], user_agent, site):
            end = mid
        else:
            start = mid

    # Linear search in the narrowed down range
    for i in range(start, end + 1):

        if user_agent_present_in_snapshot(timestamps[i], user_agent, site):
            date = timestamps[i][:8]
            formatted_date = f"{date[:4]}-{date[4:6]}-{date[6:8]}"
            # print( f"User-Agent {user_agent} was first found in robots.txt on {formatted_date}")
            return formatted_date

    # print(f"User-Agent {user_agent} was not found in the available archive.")
    return "Unretrievable"


# Test
# adaptive_binary_search("GPTBot", "time.com")
# adaptive_binary_search("CCBot", "scientificamerican.com")
# adaptive_binary_search("anthropic-ai", "scientificamerican.com")
# adaptive_binary_search("Google-Extended", "scientificamerican.com")

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python3 utils/crawlerBlockDate.py <bot_name> <url>")
        sys.exit(1)
    botName = sys.argv[1]
    url = sys.argv[2]

    print(adaptive_binary_search(botName, url))
