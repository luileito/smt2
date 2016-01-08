If you already know the WordPress CMS, then the concepts described on this page will be familiar for you.


# Roles overview #

Typically, the person that installed (smt)<sup>2</sup> on your server (maybe you?) has the superuser **root** account. Think as the **root** as the WordPress Administrator Role: that user can do anything with the CMS.

The superuser is part of the predefined _admin_ role. However, in many cases, the **root** user may choose to assign other users the _admin_ role. In that case, those users will have access to _almost all_ CMS features.

Here are summarized the capabilities of the _admin_ role and the **root** user:
| **capability** | **root user** | **admin role** |
|:---------------|:--------------|:---------------|
| create users   | _yes_         | _yes_          |
| delete users   | _yes_         | _no_           |
| create roles   | _yes_         | _yes_          |
| assign roles   | _yes_         | _no_           |
| delete roles   | _yes_         | _no_           |
| describe roles | _yes_         | _partial_      |
| update roles   | _yes_         | _partial_      |
| change users roles | _yes_         | _partial_      |
| customize CMS  | _yes_         | _partial_      |
| delete tracking logs | _yes_         | _no_           |

On the remaining extensions, the _admin_ role behave as the **root** user.

Other roles will have only access to those extensions that the **root** (or the _admin_ role in some cases) have defined.

Note that the _admin_ role cannot be deleted. When any other role is deleted, all users who were assigned to that role will revert to an empty role. That means that they won't be able to access to any section but the _Dashboard_.