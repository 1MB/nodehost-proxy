The Config files are now forbidden and the system can set a API Key we send on all requests, and can only be changed WITH the active key. Keep in mind once you set the AuthToken no API calls will work without it added like &token=codeheretosend

https://github.com/1mbsite/nodehost-proxy

## Get SSL status
```
?api=check_active_ssl
```

## Get Proxy version
```
?api=get_proxy_version
```

## Get Proxy Username
```
?api=get_proxy_username
```

## Get Proxy Rules WWW Redirect
```
?api=get_proxy_rules_wwwredirect
```

## Get Proxy Rules Cache
```
?api=get_proxy_rules_cache
```

## Get Proxy AuthToken
Will only return true or false not the code.
```
?api=get_proxy_authtoken
```

## Set Proxy Username
```
?api=set_proxy_username&username=newusername
```

## Set Proxy Rules WWW Redirect
Values can be www to redirect to www, false to do no redirects, and root to redirect from www to root every time.
```
?api=set_proxy_rules_wwwredirect&rule=www
```

## Set Proxy Rules Cache
```
?api=set_proxy_rules_cache&rule=true
```

## Set Proxy AuthToken
```
?api=set_proxy_authtoken&token=codeheretosend
```

## Update Proxy AuthToken
```
?api=set_proxy_authtoken&token=codeheretosend&oldtoken=theoldcode
```

# Server side checks we send

## From within PHP check the AUTH code.
```
$authcode = $_SERVER['HTTP_X_AUTHTOKEN'];
```

## From within PHP see if we send the request
Will be true or false as a string so match `if ($fromnh=="true"){}`.
```
$fromnh = $_SERVER['HTTP_X_PROXY_REFNH'];
```
