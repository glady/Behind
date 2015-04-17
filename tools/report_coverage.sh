#!/bin/bash

CODECLIMATE_REPO_TOKEN=99a93b7c64a586c9207ad11a8da9a7dbc2811936dc13c11bbe71653e0fcd9f53 vendor/bin/test-reporter --stdout > codeclimate.json
"curl -X POST -d @codeclimate.json -H 'Content-Type: application/json' -H 'User-Agent: Code Climate (PHP Test Reporter v0.1.1)' https://codeclimate.com/test_reports"
