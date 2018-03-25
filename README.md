# fr.aquaparisplongee.ovhmailinglistapi
OVH mailinglist API for CiviCRM to modify the subscription to OVH hosted mailing
list through the API. It use the OVH API the update teh subscription. Credential
should be generated outside of the extension.

The entity for the OVH mailinglist API is OVHMailingList and the action is
Modify.
Parameters for the api are specified below:
- contact_id: list of contacts IDs to modify the subscription (separated by ",")
template_id:
- list_name: name of the mailing list (from_email)
- list_domain: the domain of the liste (from_name)
- modify: action to do add/remove (alternative_receiver_address)
- case_id: **optional** adds the e-mail to the case identified by this ID.

    
