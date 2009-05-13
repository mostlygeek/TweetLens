function trackAnalytics(path) {
    try {
        pageTracker._trackPageview(path);
    } catch(err) {
    }
}

