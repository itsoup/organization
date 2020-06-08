# ITsoup's Organization domain service

(badges here)

**Overview**

* Aggregators for Customers’ information.
* Authorization and authentication of Users, via Roles and Permissions.

Customer resource is related to an individual or a company. By itself, a Customer resource is an aggregator for all its information registered in the software.

The User resource is an account with authorization to access some, or all, information of a Customer and it's directly associated with a real person. But a User resource isn’t required to belong to a Customer, and when that’s the case, the User must be considered a system operator. This is to differentiate the service provider from the service receiver, but since they’re all Users, and they share many of the same functionality, there’s no point in separating them in different modules.

What defines the User resource, however, is the associated Role, which is the access control layer that'll be responsible to control what each User resource can access, as well as what actions can they do in the software in the name of a Customer.

Each User resource will have, at least, one Role. If a User has many Roles, then the resulting access rights will be the sum of all Roles' permissions with favor for the active flags in case many Roles have the same permission configured. E.g.: if a User has two Roles associated (role_1 and role_2) and if the role_1 has permission_1 activated, but role_2 has permission_1 deactivated, the resulting state for permission_1 will be activated.

All available permissions will be pre-populated with a default structure, non-personalizable. When a User is considered a system’s operator will have a slightly different permissions’ structure.

## Install instructions
