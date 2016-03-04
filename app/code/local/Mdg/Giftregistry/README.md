http://www.magedevguide.com/
https://github.com/amacgregor/mdg_giftreg

Gift Registry: 
we want to extend Magento to allow customers to create gift registry lists and share them with friends and family. Customers should be able to create multiple gift registries and specify the recipients of those gift registries.
- Type: wedding, birthday, baby shower ...

Features:
•  Store administrator can define multiple event types (birthdays, weddings, and gift registries)
•  Createevents and assign multiple gift registry lists to each event
•  Customers can add products to their registries from the cart, wish list, or directly from the product pages
•  Customers can have multiple gift registries
•  People can share their registries with friends and family through e-mail and/or direct link
•  Friends and family can buy the items from the gift registry

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
