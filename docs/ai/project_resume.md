# StatusFlow

This project is a api monitor health checker, like uptime robot, the ideia is check if api is up and save response time metric

The application be separated API (PHP/Laravel) and Frontend (Next.js)

## MVP

Below I'll describe functionalities can be develop by MVP project

1. Register/Authentication
- Register
- Login
- Forgot password
- E-mail confirmation

2. Dashboard
- Empty state screen
- General status (how much monitors active)
- How much up
- How much down
- Last incidents

3. Create monitor
- Name
- URL
- Method (GET/POST)
- Interval (1 min, 5 min, 10 min)
- Timeout
- Quantity fails before alert
- E-mail to notify

4. Monitor details screen
- Response time chart
- Checks history
- Status timeline (green or red)
- Percentage uptime last 7 days
- Last fail
- Pause monitor button

5. Account settings
- Change password
- Remove account
