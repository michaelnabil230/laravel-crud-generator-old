## Options

### CRUD Options:

| Option    | Description |
| ---       | ---     |
| `--fields` | The field names for the form. e.g. ```--fields='title#string; content#text; category#select#options={"technology": "Technology", "tips": "Tips", "health": "Health"}; user_id#integer#unsigned'``` |
| `--fields-from-file` | Fields from a JSON file. e.g. `--fields-from-file="/path/to/fields.json"` |
| `--validations` | Validation rules for the fields "col_name#rules_set" e.g. ` "title#min:10|max:30|required" ` - See https://laravel.com/docs/master/validation#available-validation-rules |
| `--controller-namespace` | The namespace of the controller - sub directories will be created |
| `--foreign-keys` | Any foreign keys for the table. e.g. `--foreign-keys="user_id#id#users#cascade"` where user_id is the column name, id is the name of the field on the foreign table, users is the name of the foreign table, and cascade is the operation 'ON DELETE' together with 'ON UPDATE' |
| `--relationships` | The relationships for the model. e.g. `--relationships="comments#hasMany#App\Comment"` in the format |
| `--route` | Include Crud route to routes.php? yes or no |
| `--route-group` | Prefix of the route group |
| `--view-path` | The name of the view path |
| `--localize` | Allow to localize. e.g. localize=yes  |
| `--locales`  | Locales language type. e.g. locals=en |
| `--soft-deletes` | Include soft deletes fields. eg. `--soft-deletes=yes` |


### Controller Options:

| Option    | Description |
| ---       | ---     |
| `--model` | The name of the model. e.g. ```--model="Post"``` |
| `--controller-namespace` | The namespace of the controller. e.g. ```--controller-namespace="Http\Controllers\Client"``` |
| `--view-path` | The name of the view path |
| `--fields` | The field names for the form. e.g. ```--fields='title#string; content#text; category#select#options={"technology": "Technology", "tips": "Tips", "health": "Health"}; user_id#integer#unsigned'``` |
| `--validations` | Validation rules for the fields "col_name#rules_set" e.g. ``` "title#min:10|max:30|required" ``` - See https://laravel.com/docs/master/validation#available-validation-rules |
| `--route-group` | Prefix of the route group |
| `--force` | Overwrite already existing controller. |

### View Options:

| Option    | Description |
| ---       | ---     |
| `--fields` | The field names for the form. e.g. ```--fields='title#string; content#text; category#select#options={"technology": "Technology", "tips": "Tips", "health": "Health"}; user_id#integer#unsigned'``` |
| `--view-path` | The name of the view path |
| `--route-group` | Prefix of the route group |
| `--validations` | Validation rules for the form "col_name#rules_set" e.g. ``` "title#min:10|max:30|required" ``` - See https://laravel.com/docs/master/validation#available-validation-rules |
| `--custom-data` | Some additional values to use in the crud. |
| `--localize` | Allow to localize. e.g. localize=yes  |

### Model Options:

| Option    | Description |
| ---       | ---     |
| `--table` | The name of the table |
| `--fillable` | The name of the view path |
| `--relationships` | The relationships for the model. e.g. `--relationships="comments#hasMany#App\Comment"` in the format |
| `--soft-deletes` | Include soft deletes fields. eg. `--soft-deletes=yes` |

### Migration Options:

| Option    | Description |
| ---       | ---     |
| `--schema` | The name of the schema |
| `--foreign-keys` | Any foreign keys for the table. e.g. `--foreign-keys="user_id#id#users#cascade"` where user_id is the column name, id is the name of the field on the foreign table, users is the name of the foreign table, and cascade is the operation 'ON DELETE' together with 'ON UPDATE' |
| `--soft-deletes` | Include soft deletes fields. eg. `--soft-deletes=yes` |

### Lang Options:

| Option    | Description |
| ---       | ---     |
| `--fields` | The field names for the form. e.g. ```--fields='title#string; content#text``` |
| `--locales`  | Locales language type. e.g. locals=en |

### API CRUD Options:

| Option    | Description |
| ---       | ---     |
| `--fields` | The field names for the form. e.g. ```--fields='title#string; content#text; category#select#options={"technology": "Technology", "tips": "Tips", "health": "Health"}; user_id#integer#unsigned'``` |
| `--fields-from-file` | Fields from a JSON file. e.g. `--fields-from-file="/path/to/fields.json"` |
| `--validations` | Validation rules for the fields "col_name#rules_set" e.g. ` "title#min:10|max:30|required" ` - See https://laravel.com/docs/master/validation#available-validation-rules |
| `--controller-namespace` | The namespace of the controller - sub directories will be created |
| `--foreign-keys` | Any foreign keys for the table. e.g. `--foreign-keys="user_id#id#users#cascade"` where user_id is the column name, id is the name of the field on the foreign table, users is the name of the foreign table, and cascade is the operation 'ON DELETE' together with 'ON UPDATE' |
| `--relationships` | The relationships for the model. e.g. `--relationships="comments#hasMany#App\Comment"` in the format |
| `--route` | Include Crud route to routes.php? yes or no |
| `--route-group` | Prefix of the route group |
| `--soft-deletes` | Include soft deletes fields. eg. `--soft-deletes=yes` |

### API Controller Options:

| Option    | Description |
| ---       | ---     |
| `--model` | The name of the model. e.g. ```--model="Post"``` |
| `--controller-namespace` | The namespace of the controller. e.g. ```--controller-namespace="Http\Controllers\Client"``` |
| `--validations` | Validation rules for the fields "col_name#rules_set" e.g. ``` "title#min:10|max:30|required" ``` - See https://laravel.com/docs/master/validation#available-validation-rules |
| `--force` | Overwrite already existing controller. |

[&larr; Back to index](README.md)
