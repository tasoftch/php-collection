# PHP Collection Library
Works with arrays and offers you a bunch of features:
- Order collections with several sort descriptors
- Create tagged collections, so each element can have tags
- Create priority collections that are ordered automatically against element priority
- Create dependency collection, elements can depend on others

````php
$collection = new DefaultCollection(
  [
    "echo",
    "alpha",
    "delta",
    "charlie",
    "bravo"
  ]
);
$collection->sort([
  new DefaultSortDescriptor(true) // Sort ascending
]);

print_r( $collection->toArray() ); // [alpha, bravo, charlie, delta, echo]
````
