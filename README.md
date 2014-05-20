# Future address changes

This extensions adds functionality to change an address in the future, the old address is archived as an activity in civicrm.

## How does it works?

You set for future addresses (those are location types, see later on) the change date. 
As soon as the change date is passed this address is changed to the correspondent location type and the old addres
is archived as an activity.

The location types you can use should have a name which start with *new_* For example a location type with the `name new_Home` and `label Future home` is used for entering a future address for the location type Home.

## Set up future addresses

1. Configure location types for the future with a name which starts with **new_**. E.g. the location type with the name new_Home correspondents to the location type Home.

## Set up temporarily addresses

1. Configure a location type for temporarily with a name which start with **temp_** E.g. the location type with the name temp_Home correspondents to the location type Home.
2. Configure a corresponding future location type (see above).


## Developers

As a developer you can extend this extension by implementing hooks. See the description of the hooks at [docs/hooks.md](docs/hooks.md)

