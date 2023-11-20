export function validateAndFixUrl(inputUrl) {
  try {
    if (!/^http(s)?:\/\//i.test(inputUrl)) {
      inputUrl = 'https://' + inputUrl;
    }

    const url = new URL(inputUrl);

    if (!url.hostname.includes('.')) {
      url.hostname = url.hostname + ".com";
    }

    return url.href;
  } catch (err) {
    return null;
  }
}
export function sortAgents(agents) {
  return agents.sort((a, b) => {
    if (a.userAgent === '*') return -1;
    if (b.userAgent === '*') return 1;
    return 0;
  });
}