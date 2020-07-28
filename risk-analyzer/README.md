# Risk Analyzer API

## Overview

This API is the implementation for the requirements described at [Origin Backend Take-Home Assignment](../README.md). 
It was developed by using PHP, Lumen and Docker and it had simplicity and maintainability as its main idea.

## How to get it running

All you need is the Docker installed in your machine. In the root of the project, there is the file [run.sh](../run.sh) which contains the commands to get the project running, you may execute the file or the commands directly on the terminal.

Once the containers are up and running, the API is accessible at `localhost/`.

## How to send a request

The API has only one endpoint available `localhost/api/risk-analyzer` and it accepts the method POST.
You can use the given example:

```JSON
{
  "age": 35,
  "dependents": 2,
  "house": {"ownership_status": "owned"},
  "income": 0,
  "marital_status": "married",
  "risk_questions": [0, 1, 0],
  "vehicle": {"year": 2018}
}
```

The parameters `vehicle` and `house` are optional.
