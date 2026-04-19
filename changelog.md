### 6.2.4

- add `merch_acct_id` setting and include it in gateway requests (fixes "No merchant account configured")
- fix `inovio_logger` call so debug logs are actually written on authentication failures
- fix `to_url_encode` to use `http_build_query` so credentials with special characters are sent correctly
- remove leftover `die("sdsd")` debug statement in `InovioServiceConfig`

### 6.2.2

- upgrade threeDS version now using 2.2.0
