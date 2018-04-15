# fr.aquaparisplongee.ovhmailinglistapi
OVH mailinglist API for CiviCRM to manage the subscription to OVH hosted mailing
list through the API. It use the OVH API to update the subscription. Credential
should be generated outside of the extension.

The entity for the OVH mailinglist API is OVHMailingList and the action is
Modify or Sync.

Parameters for the modify api are specified below:
- contact_id: list of contacts IDs to modify the subscription (separated by ",")
- list_name: name of the mailing list (from_email)
- list_domain: the domain of the liste (from_name)
- modify: action to do add/remove (alternative_receiver_address)
- case_id: **optional** adds the e-mail to the case identified by this ID.


Parameters for the sync api are specified below:
- group_id: group ID to sync with the mailing list
- list_name: name of the mailing list (from_email)
- list_domain: the domain of the liste (from_name)
- modify: action to do add/remove (alternative_receiver_address)
- case_id: **optional** adds the e-mail to the case identified by this ID.

