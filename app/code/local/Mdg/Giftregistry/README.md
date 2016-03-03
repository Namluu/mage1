Gift Registry: 
- Type: wedding, birthday, baby shower ...

Models:
- Registry Model: This model is used to store the gift registry information, such as gift registry type, address, and recipient information.
- Registry Item: This model is used to store the information of each of the gift registry items (quantity requested, quantity bought, product_id).

Database tables:
- Registry Entity: Thistable is used to store the gift registry and event information
- Registry Type: By storing the gift registry type into a separate table, we can add or remove event types
- Registry Item: Thistable is used to store the information of each of the gift registry items (quantity requested, quantity bought, product_id)

Step by step:
- Mdg/Giftregistry/sql/*: setup table
- Mdg/Giftregistry/data/*: insert database
- controllers/*: router
