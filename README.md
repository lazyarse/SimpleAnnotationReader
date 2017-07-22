# Simple Annotation Reader

A simple annotation reader that parses class and property annotations into arrays

- `@Foo` becomes `['foo']`
- `@foo(arg1,arg2)` becomes `['foo'=>['arg1','arg2']]`
- `@foo(x=y,arg1)` becomes `['foo' => ['x' => 'y', 'arg1']]`

## Requirements

Annotations must:
- be placed on a new line
- start with an '@'
- start inside a comment block, started with /** and ending with */
- be case insensitive. All annotations are converted to lowercase.
- Not be deeper than one level, e.g. `@foo(type=bar)` is valid, whilst `@foo(type=[x=y])` is not valid.
