// serializeJSON
describe("$.serializeJSON", function () {
  var obj, $form;

  it('accepts a jQuery or Zepto object with a form', function() {
    $form = $('<form>');
    $form.append($('<input type="text" name="1" value="1"/>'));
    $form.append($('<input type="text" name="2" value="2"/>'));
    obj = $form.serializeJSON();
    expect(obj).toEqual({"1": "1", "2": "2"});
  });

  if ($.fn.jquery) { // not supported on Zepto

    it('accepts a jQuery object with inputs', function() {
      $inputs = $('<input type="text" name="1" value="1"/>').add($('<input type="text" name="2" value="2"/>'));
      obj = $inputs.serializeJSON();
      expect(obj).toEqual({"1": "1", "2": "2"});
    });

    it('accepts a jQuery object with forms and inputs', function() {
      var $form1, $form2, $els;
      $form1 = $('<form>');
      $form1.append($('<input type="text" name="1" value="1"/>'));
      $form1.append($('<input type="text" name="2" value="2"/>'));
      $form2 = $('<form>');
      $form2.append($('<input type="text" name="3" value="3"/>'));
      $form2.append($('<input type="text" name="4" value="4"/>'));
      $inputs = $('<input type="text" name="5" value="5"/>').add($('<input type="text" name="6" value="6"/>'));
      $els = $form1.add($form2).add($inputs);
      obj = $els.serializeJSON();
      expect(obj).toEqual({"1": "1", "2": "2", "3": "3", "4": "4", "5": "5", "6": "6"});
    });
  }

  describe('with simple one-level attributes', function() {
    beforeEach(function() {
      $form = $('<form>');
      $form.append($('<input type="text"  name="firstName" value="Mario"/>'));
      $form.append($('<input type="text"  name="lastName"  value="Izquierdo"/>'));
    });

    it("serializes into plain attributes", function() {
      obj = $form.serializeJSON();
      expect(obj).toEqual({
        firstName: "Mario",
        lastName: "Izquierdo"
      });
    });
  });

  describe('with nested object attributes', function() {
    beforeEach(function() {
      $form = $('<form>');
      $form.append($('<input type="text"  name="address[city]"         value="San Francisco"/>'));
      $form.append($('<input type="text"  name="address[state][name]"  value="California"/>'));
      $form.append($('<input type="text"  name="address[state][abbr]"  value="CA"/>'));
    });

    it("serializes into nested object attributes", function() {
      obj = $form.serializeJSON();
      expect(obj).toEqual({
        address: {
          city: "San Francisco",
          state: {
            name: "California",
            abbr: "CA"
          }
        }
      });
    });
  });

  describe('with empty brackets (arrays)', function() {
    beforeEach(function() {
      $form = $('<form>');
      $form.append($('<input type="text"  name="jobbies[]" value="code"/>'));
      $form.append($('<input type="text"  name="jobbies[]" value="climbing"/>'));
    });

    it("pushes elements into an array", function() {
      obj = $form.serializeJSON();
      expect(obj).toEqual({
        jobbies: ['code', 'climbing']
      });
    });
  });

  describe('with attribute names that are integers', function() {
    beforeEach(function() {
      $form = $('<form>');
      $form.append($('<input type="text"  name="foo[0]"    value="zero"/>'));
      $form.append($('<input type="text"  name="foo[1]"    value="one"/>'));
      $form.append($('<input type="text"  name="foo[2][0]" value="two-zero"/>'));
      $form.append($('<input type="text"  name="foo[2][1]" value="two-one"/>'));
    });

    it("still creates objects with keys that are strings", function() {
      obj = $form.serializeJSON();
      expect(obj).toEqual({
        'foo': {
          '0': 'zero',
          '1': 'one',
          '2': {
            '0': 'two-zero',
            '1': 'two-one'
          }
        }
      });
    });
  });

  describe('with a select multiple', function() {
    it("serializes all the selected elements", function() {
      $form = $('<form>');
      $form.append($('<select name="camels[]" multiple><option value="1" selected>1</option><option value="2">2</option><option value="3" selected>3</option></select>'));
      obj = $form.serializeJSON();
      expect(obj).toEqual({camels: ['1','3']}); // selected elements included as an array
    });
    it("ignores the field if nothing is selected", function() {
      $form = $('<form>');
      $form.append($('<select name="camels[]" multiple><option value="1">1</option><option value="2">2</option><option value="3">3</option></select>'));
      obj = $form.serializeJSON();
      expect(obj).toEqual({ }); // nothing is serialized
    });
    it("can be set to empty string using a hidden field", function() {
      $form = $('<form>');
      $form.append($('<input type="hidden" name="camels:array" value="[]" />'));
      $form.append($('<select name="camels[]" multiple><option value="1">1</option><option value="2">2</option><option value="3">3</option></select>'));
      obj = $form.serializeJSON();
      expect(obj).toEqual({camels: []}); // empty list
    });
  });

  describe('with complext array of objects', function() {
    beforeEach(function() {
      $form = $('<form>');
      $form.append($('<input type="text"  name="projects[][name]"        value="serializeJSON" />'));
      $form.append($('<input type="text"  name="projects[][language]"    value="javascript" />'));

      $form.append($('<input type="text"  name="projects[][name]"        value="bettertabs" />'));
      $form.append($('<input type="text"  name="projects[][language]"    value="ruby" />'));

      $form.append($('<input type="text"  name="projects[][name]"        value="formwell" />'));
      $form.append($('<input type="text"  name="projects[][languages][]" value="coffeescript" />'));
      $form.append($('<input type="text"  name="projects[][languages][]" value="javascript" />'));
    });

    it("serializes into array of objects", function() {
      obj = $form.serializeJSON();
      expect(obj).toEqual({
        projects: [
          { name: "serializeJSON", language: "javascript" },
          { name: "bettertabs",    language: "ruby" },
          { name: "formwell",      languages: ["coffeescript", "javascript"] },
        ]
      });
    });
  });

  describe("with existing properties", function() {
    beforeEach(function() {
      $form = $('<form>');
      $form.append($('<input type="text"   name="str" value="String" />'));
      $form.append($('<input type="text"   name="str" value="String Override" />'));

      $form.append($('<input type="text"   name="array" value="a string that was there before" />'));
      $form.append($('<input type="text"   name="array[]" value="one" />'));
      $form.append($('<input type="text"   name="array[]" value="two" />'));

      $form.append($('<input type="text"   name="crosstype"         value="str" />'));
      $form.append($('<input type="text"   name="crosstype:number"  value="2" />'));
      $form.append($('<input type="text"   name="crosstype:boolean" value="true" />'));

      $form.append($('<input type="hidden" name="object"                 value=""/>'));
      $form.append($('<input type="text"   name="object[nested]"         value="blabla" />'));
      $form.append($('<input type="text"   name="object[nested][nested]" value="final value" />'));
    });

    it("overrides to keep the last property value", function() {
      obj = $form.serializeJSON();
      expect(obj).toEqual({
        str: "String Override",
        array: ["one", "two"],
        crosstype: true,
        object: { nested: { nested: "final value" }}
      });
    });
  });

  describe('unchecked checkboxes', function() {
    it('are ignored by default (same as regural HTML forms and the jQuery.serializeArray function)', function() {
      $form = $('<form>');
      $form.append($('<input type="checkbox" name="check1" value="yes"/>'));
      $form.append($('<input type="checkbox" name="check2" value="yes"/>'));
      obj = $form.serializeJSON();
      expect(obj).toEqual({}); // empty because unchecked checkboxes are ignored
    });

    it('are ignored also in arrays', function() {
      $form = $('<form>');
      $form.append($('<input type="checkbox" name="flags[]" value="green"/>'));
      $form.append($('<input type="checkbox" name="flags[]" value="red"/>'));
      obj = $form.serializeJSON();
      expect(obj).toEqual({});
    });

    it('could use a hidden field and a custom parser to force an empty array in an array of unchecked checkboxes', function() {
      $form = $('<form>');
      $form.append($('<input type="hidden"   name="flags" value="[]"/>'));
      $form.append($('<input type="checkbox" name="flags[]" value="green"/>'));
      $form.append($('<input type="checkbox" name="flags[]" value="red"/>'));
      obj = $form.serializeJSON({parseWithFunction: function(val){ return val == '[]' ? [] : val }});
      expect(obj).toEqual({'flags': []});

      $form.find('input[value="red"]').prop('checked', true);
      obj = $form.serializeJSON({parseWithFunction: function(val){ return val == '[]' ? [] : val }});
      expect(obj).toEqual({'flags': ['red']});
    });

    it('could use a hidden field with type :array to force an empty array in an array of unchecked checkboxes', function() {
      $form = $('<form>');
      $form.append($('<input type="hidden"   name="flags:array" value="[]"/>'));
      $form.append($('<input type="checkbox" name="flags[]" value="green"/>'));
      $form.append($('<input type="checkbox" name="flags[]" value="red"/>'));
      obj = $form.serializeJSON();
      expect(obj).toEqual({'flags': []});

      $form.find('input[value="red"]').prop('checked', true);
      obj = $form.serializeJSON();
      expect(obj).toEqual({'flags': ['red']});
    });

    it('can be combined with hidden fields to set the false value', function() {
      $form = $('<form>');
      $form.append($('<input type="hidden"    name="truthy" value="0"/>'));
      $form.append($('<input type="checkbox"  name="truthy" value="1" checked="checked"/>')); // should keep "1"
      $form.append($('<input type="hidden"    name="falsy"  value="0"/>'));
      $form.append($('<input type="checkbox"  name="falsy"  value="1"/>')); // should keep "0", from the hidden field
      obj = $form.serializeJSON();
      expect(obj).toEqual({
        truthy: '1', // from the checkbok
        falsy:  '0'  // from the hidden field
      });
    });

    it('should be ignored if they have no name', function() {
      $form = $('<form>');
      $form.append($('<input type="checkbox" value="yes"/>'));
      $form.append($('<input type="checkbox" value="yes"/>'));
      obj = $form.serializeJSON({checkboxUncheckedValue: 'NOPE'});
      expect(obj).toEqual({});
    });

    it('use the checkboxUncheckedValue option if defined', function() {
      $form = $('<form>');
      $form.append($('<input type="checkbox" name="check1" value="yes"/>'));
      $form.append($('<input type="checkbox" name="check2" value="yes"/>'));
      obj = $form.serializeJSON({checkboxUncheckedValue: 'NOPE'});
      expect(obj).toEqual({check1: 'NOPE', check2: 'NOPE'});
    });

    it('use the attr data-unchecked-value if defined', function() {
      $form = $('<form>');
      $form.append($('<input type="checkbox" name="check1" value="yes"/>')); // ignored
      $form.append($('<input type="checkbox" name="check2" value="yes" data-unchecked-value="NOPE"/>')); // with data-unchecked-value uses that value
      obj = $form.serializeJSON(); // NOTE: no checkboxUncheckedValue used
      expect(obj).toEqual({check2: 'NOPE'});
    });
  });

  describe('value types', function() {
    describe(':number', function() {
      it('parses numbers', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="i1:number" value="10"/>'));
        $form.append($('<input type="text" name="i2:number" value="10.5"/>'));
        $form.append($('<input type="text" name="un"        value="10"/>'));
        obj = $form.serializeJSON();
        expect(obj).toEqual({i1: 10, i2: 10.5, un: '10'});
      });
      it('parses non numbers to NaN', function(){
        $form = $('<form>');
        $form.append($('<input type="text" name="i1:number" value="text"/>'));
        $form.append($('<input type="text" name="i2:number" value="null"/>'));
        $form.append($('<input type="text" name="i3:number" value="false"/>'));
        obj = $form.serializeJSON();
        expect(obj).toEqual({i1: NaN, i2: NaN, i3: NaN});
      });
    });

    describe(':boolean', function() {
      it('parses anything that looks truthy to true', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1:boolean" value="true"/>'));
        $form.append($('<input type="text" name="b2:boolean" value="TRUE"/>'));
        $form.append($('<input type="text" name="b3:boolean" value="yes"/>'));
        $form.append($('<input type="text" name="b4:boolean" value="[1,2,3]"/>'));
        $form.append($('<input type="text" name="b5:boolean" value="Bla bla bla bla ..."/>'));
        obj = $form.serializeJSON();
        expect(obj).toEqual({b1: true, b2: true, b3: true, b4: true, b5: true});
      });
      it('parses anything that looks falsy to false', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1:boolean" value="false"/>'));
        $form.append($('<input type="text" name="b2:boolean" value="null"/>'));
        $form.append($('<input type="text" name="b3:boolean" value="undefined"/>'));
        $form.append($('<input type="text" name="b4:boolean" value=""/>'));
        $form.append($('<input type="text" name="b5:boolean" value="0"/>'));
        obj = $form.serializeJSON();
        expect(obj).toEqual({b1: false, b2: false, b3: false, b4: false, b5: false});
      });
    });
    describe(':null', function() {
      it('parses anything that looks falsy to null', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1:null" value="false"/>'));
        $form.append($('<input type="text" name="b2:null" value="null"/>'));
        $form.append($('<input type="text" name="b3:null" value="undefined"/>'));
        $form.append($('<input type="text" name="b4:null" value=""/>'));
        $form.append($('<input type="text" name="b5:null" value="0"/>'));
        obj = $form.serializeJSON();
        expect(obj).toEqual({b1: null, b2: null, b3: null, b4: null, b5: null});
      });
      it('keeps anything that looks truthy as string', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1:null" value="true"/>'));
        $form.append($('<input type="text" name="b2:null" value="TRUE"/>'));
        $form.append($('<input type="text" name="b3:null" value="yes"/>'));
        $form.append($('<input type="text" name="b4:null" value="[1,2,3]"/>'));
        $form.append($('<input type="text" name="b5:null" value="Bla bla bla bla ..."/>'));
        obj = $form.serializeJSON();
        expect(obj).toEqual({b1: 'true', b2: 'TRUE', b3: 'yes', b4: '[1,2,3]', b5: "Bla bla bla bla ..."});
      });
    });
    describe(':string', function() {
      it('keeps everything as string', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1:string" value="true"/>'));
        $form.append($('<input type="text" name="b2:string" value="TRUE"/>'));
        $form.append($('<input type="text" name="b3:string" value="yes"/>'));
        $form.append($('<input type="text" name="b4:string" value="[1,2,3]"/>'));
        $form.append($('<input type="text" name="b5:string" value="Bla bla bla bla ..."/>'));
        obj = $form.serializeJSON();
        expect(obj).toEqual({b1: 'true', b2: 'TRUE', b3: 'yes', b4: '[1,2,3]', b5: "Bla bla bla bla ..."});
      });
      it('is useful to override other parse options', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1:string" value="true"/>'));
        $form.append($('<input type="text" name="b2:string" value="1"/>'));
        $form.append($('<input type="text" name="b3:string" value="null"/>'));
        $form.append($('<input type="text" name="b4:string" value=""/>'));
        obj = $form.serializeJSON({parseAll: true, parseWithFunction: function(val){return val === '' ? null : val}});
        expect(obj).toEqual({b1: 'true', b2: '1', b3: 'null', b4: ''});
      });
    });
    describe(':array', function() {
      it('parses arrays with JSON.parse', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1:array" value="[]"/>'));
        $form.append($('<input type="text" name="b2:array" value=\'["my", "stuff"]\'/>'));
        $form.append($('<input type="text" name="b3:array" value="[1,2,3]"/>'));
        $form.append($('<input type="text" name="b4:array" value="[1,[2,[3]]]"/>'));
        obj = $form.serializeJSON();
        expect(obj).toEqual({b1: [], b2: ['my', 'stuff'], b3: [1,2,3], b4: [1,[2,[3]]]});
      });
      it('raises an error if the array can not be parsed', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1:array" value="<NOT_AN_ARRAY>"/>'));
        expect(function(){$form.serializeJSON()}).toThrow();
      });
    });
    describe(':object', function() {
      it('parses objects with JSON.parse', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1:object" value="{}"/>'));
        $form.append($('<input type="text" name="b2:object" value=\'{"my": "stuff"}\'/>'));
        $form.append($('<input type="text" name="b3:object" value=\'{"my": {"nested": "stuff"}}\'/>'));
        obj = $form.serializeJSON();
        expect(obj).toEqual({b1: {}, b2: {"my": "stuff"}, b3: {"my": {"nested": "stuff"}}});
      });
      it('raises an error if the obejct can not be parsed', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1:object" value="<NOT_AN_OBJECT>"/>'));
        expect(function(){$form.serializeJSON()}).toThrow();
      });
    });
    describe(':skip', function() {
      it('removes the field from the parsed result', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1"           value="Im in"/>'));
        $form.append($('<input type="text" name="b2:skip"      value="Im out"/>'));
        $form.append($('<input type="text" name="b3[out]:skip" value="Im out"/>'));
        obj = $form.serializeJSON();
        expect(obj).toEqual({b1: 'Im in'});
      });
      it('raises an error if the obejct can not be parsed', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1:object" value="<NOT_AN_OBJECT>"/>'));
        expect(function(){$form.serializeJSON()}).toThrow();
      });
    });
    describe(':auto', function() {
      it('parses Strings, Booleans and Nulls if they look like they could be one of them (same as parseAll option)', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="Numeric 0:auto"     value="0"/>'));
        $form.append($('<input type="text" name="Numeric 1:auto"     value="1"/>'));
        $form.append($('<input type="text" name="Numeric 2.2:auto"   value="2.2"/>'));
        $form.append($('<input type="text" name="Numeric -2.25:auto" value="-2.25"/>'));
        $form.append($('<input type="text" name="Bool true:auto"     value="true"/>'));
        $form.append($('<input type="text" name="Bool false:auto"    value="false"/>'));
        $form.append($('<input type="text" name="Null:auto"          value="null"/>'));
        $form.append($('<input type="text" name="String:auto"        value="text is always string"/>'));
        $form.append($('<input type="text" name="Empty:auto"         value=""/>'));
        obj = $form.serializeJSON();
        expect(obj).toEqual({
          "Numeric 0":     0,
          "Numeric 1":     1,
          "Numeric 2.2":   2.2,
          "Numeric -2.25": -2.25,
          "Bool true":     true,
          "Bool false":    false,
          "Null":          null,
          "String":        "text is always string",
          "Empty":         ""
        });
      });
      it('does not auto-recognize arrays or objects', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="empty array:auto"  value="[]"/>'));
        $form.append($('<input type="text" name="array:auto"        value="[1,2,3]"/>'));
        $form.append($('<input type="text" name="empty object:auto" value="{}"/>'));
        $form.append($('<input type="text" name="object:auto"       value="{one: 1}"/>'));
        obj = $form.serializeJSON();
        expect(obj).toEqual({
          "empty array": "[]",
          "array": "[1,2,3]",
          "empty object": "{}",
          "object": "{one: 1}"
        }); // they are still strings
      });

    });
    describe('invalid types', function() {
      it('raises an error if the type is not known', function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="b1:kaka" value="not a valid type"/>'));
        expect(function(){ $form.serializeJSON() })
          .toThrow(new Error("serializeJSON ERROR: Invalid type kaka found in input name 'b1:kaka', please use one of string, number, boolean, null, array, object, auto, skip"));
      });
    });
    describe('form with multiple types', function() {
      it("parses every type as expected", function() { // EXAMPLE from the README file
        $form = $('<form>');
        $form.append($('<input type="text" name="notype"           value="default type is :string"/>'));
        $form.append($('<input type="text" name="string:string"    value=":string type overrides parsing options"/>'));
        $form.append($('<input type="text" name="excludes:skip"    value="Use :skip to not include this field in the result"/>'));

        $form.append($('<input type="text" name="number[1]:number"           value="1"/>'));
        $form.append($('<input type="text" name="number[1.1]:number"         value="1.1"/>'));
        $form.append($('<input type="text" name="number[other stuff]:number" value="other stuff"/>'));

        $form.append($('<input type="text" name="boolean[true]:boolean"      value="true"/>'));
        $form.append($('<input type="text" name="boolean[false]:boolean"     value="false"/>'));
        $form.append($('<input type="text" name="boolean[0]:boolean"         value="0"/>'));

        $form.append($('<input type="text" name="null[null]:null"            value="null"/>'));
        $form.append($('<input type="text" name="null[other stuff]:null"     value="other stuff"/>'));

        $form.append($('<input type="text" name="auto[string]:auto"          value="text with stuff"/>'));
        $form.append($('<input type="text" name="auto[0]:auto"               value="0"/>'));
        $form.append($('<input type="text" name="auto[1]:auto"               value="1"/>'));
        $form.append($('<input type="text" name="auto[true]:auto"            value="true"/>'));
        $form.append($('<input type="text" name="auto[false]:auto"           value="false"/>'));
        $form.append($('<input type="text" name="auto[null]:auto"            value="null"/>'));
        $form.append($('<input type="text" name="auto[list]:auto"            value="[1, 2, 3]"/>'));

        $form.append($('<input type="text" name="array[empty]:array"         value="[]"/>'));
        $form.append($('<input type="text" name="array[not empty]:array"     value="[1, 2, 3]"/>'));

        $form.append($('<input type="text" name="object[empty]:object"       value="{}"/>'));
        $form.append($('<input type="text" name="object[not empty]:object"   value=\'{"my": "stuff"}\'/>'));

        obj = $form.serializeJSON();
        expect(obj).toEqual({
          "notype": "default type is :string",
          "string": ":string type overrides parsing options",
          // :skip type removes the field from the output
          "number": {
            "1": 1,
            "1.1": 1.1,
            "other stuff": NaN, // <-- Other stuff parses as NaN (Not a Number)
          },
          "boolean": {
            "true": true,
            "false": false,
            "0": false, // <-- "false", "null", "undefined", "", "0" parse as false
          },
          "null": {
            "null": null, // <-- "false", "null", "undefined", "", "0" parse as null
            "other stuff": "other stuff"
          },
          "auto": { // works as the parseAll option
            "string": "text with stuff",
            "0": 0,         // <-- parsed as number
            "1": 1,         // <-- parsed as number
            "true": true,   // <-- parsed as boolean
            "false": false, // <-- parsed as boolean
            "null": null,   // <-- parsed as null
            "list": "[1, 2, 3]" // <-- array and object types are not auto-parsed
          },
          "array": { // <-- works using JSON.parse
            "empty": [],
            "not empty": [1,2,3]
          },
          "object": { // <-- works using JSON.parse
            "empty": {},
            "not empty": {"my": "stuff"}
          }
        });
      });
    });

    describe('data-value-type attribute', function() {
      it("should set type if field name do not contain :type definition", function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="fooData" data-value-type="alwaysBoo"   value="0"/>'));
        $form.append($('<input type="text" name="fooDataWithBrackets[kokoszka]" data-value-type="alwaysBoo"   value="0"/>'));
        $form.append($('<input type="text" name="fooDataWithBrackets[kokoszka i cos innego]" data-value-type="alwaysBoo"   value="0"/>'));
        $form.append($('<input type="text" name="foo:alwaysBoo" data-value-type="string"   value="0"/>'));
        $form.append($('<input type="text" name="notype" value="default type is :string"/>'));
        $form.append($('<input type="text" name="stringData" data-value-type="string"   value="data-value-type=string type overrides parsing options"/>'));
        $form.append($('<input type="text" name="string:string" data-value-type="boolean"   value=":string type overrides parsing options"/>'));
        $form.append($('<input type="text" name="excludes" data-value-type="skip"   value="Use :skip to not include this field in the result"/>'));
        $form.append($('<input type="text" name="numberData" data-value-type="number"   value="1"/>'));
        $form.append($('<input type="text" name="numberData[A]" data-value-type="number"        value="1"/>'));
        $form.append($('<input type="text" name="numberData[B][C]" data-value-type="number"     value="2"/>'));
        $form.append($('<input type="text" name="numberData[D][E][F]" data-value-type="number"  value="3"/>'));
        $form.append($('<input type="text" name="number" data-value-type="number"   value="1"/>'));
        $form.append($('<select name="selectNumber" data-value-type="number"><option value="1">Value 1</option><option selected value="2">Value 2</option></select>'));

        obj = $form.serializeJSON({
          customTypes: {
            alwaysBoo: function() { return "Boo" }
          }
        });

        expect(obj).toEqual({
          "fooDataWithBrackets": {
            kokoszka: "Boo",
            "kokoszka i cos innego": "Boo"
          },
          "fooData": "Boo",
          "foo": "Boo",
          "notype": "default type is :string",
          "stringData": "data-value-type=string type overrides parsing options",
          "string": ":string type overrides parsing options",
          "numberData": { A: 1, B: { C: 2 }, D: { E: { F: 3 } } },
          "number": 1,
          "selectNumber": 2
        });
      });

      if ($.fn.jquery) { // not supported on Zepto
        it("also works for matched inputs (not just forms) if they have the data-value-type attribute", function () {
          $inputs = $(
            '<input type="text" name="fooData" data-value-type="alwaysBoo"   value="0"/>' +
            '<input type="text" name="foo:alwaysBoo" data-value-type="string"   value="0"/>' +
            '<input type="text" name="notype" value="default type is :string"/>' +
            '<input type="text" name="stringData" data-value-type="string"   value="data-value-type=string type overrides parsing options"/>' +
            '<input type="text" name="number" data-value-type="number"   value="1"/>'
          );

          obj = $inputs.serializeJSON({
            customTypes: {
              alwaysBoo: function() { return "Boo" }
            }
          });

          expect(obj).toEqual({
            "fooData": "Boo",
            "foo": "Boo",
            "notype": "default type is :string",
            "stringData": "data-value-type=string type overrides parsing options",
            "number": 1
          });
        });
      }
    });

    describe('data-skip-falsy attribute', function() {
      it("allows to skip faily fields, just like with the option skipFalsyValuesForFields", function() {
        $form2 = $('<form>');
        $form2.append($('<input type="text" name="skipFalsyZero:number"    data-skip-falsy="true"  value="0"/>'));
        $form2.append($('<input type="text" name="skipFalsyFalse:boolean"  data-skip-falsy="true"  value="false"/>'));
        $form2.append($('<input type="text" name="skipFalsyNull:null"      data-skip-falsy="true"  value="null"/>'));
        $form2.append($('<input type="text" name="skipFalsyEmpty:string"   data-skip-falsy="true"  value=""/>'));
        $form2.append($('<input type="text" name="skipFalsyFoo:string"     data-skip-falsy="true"  value="foo"/>'));
        $form2.append($('<input type="text" name="zero:number"  value="0"/>'));
        $form2.append($('<input type="text" name="foo:string"   value="foo"/>'));
        $form2.append($('<input type="text" name="empty:string" value=""/>'));

        obj = $form2.serializeJSON();
        expect(obj["skipFalsyZero"]).toEqual(undefined);  // skip
        expect(obj["skipFalsyFalse"]).toEqual(undefined); // skip
        expect(obj["skipFalsyNull"]).toEqual(undefined);  // skip
        expect(obj["skipFalsyEmpty"]).toEqual(undefined); // skip
        expect(obj["skipFalsyFoo"]).toEqual("foo");
        expect(obj["zero"]).toEqual(0);
        expect(obj["foo"]).toEqual("foo");
        expect(obj["empty"]).toEqual("");
      });

      it("overrides the option skipFalsyValuesForFields", function() {
        $form2 = $('<form>');
        $form2.append($('<input type="text" name="skipFalsyZero:number"    data-skip-falsy="true"  value="0"/>'));
        $form2.append($('<input type="text" name="skipFalsyFalse:boolean"  data-skip-falsy="false" value="false"/>'));
        $form2.append($('<input type="text" name="skipFalsyNull:null"      data-skip-falsy="false" value="null"/>'));
        $form2.append($('<input type="text" name="skipFalsyEmpty:string"   data-skip-falsy="true"  value=""/>'));
        $form2.append($('<input type="text" name="skipFalsyFoo:string"     data-skip-falsy="true"  value="foo"/>'));
        $form2.append($('<input type="text" name="zero:number"  value="0"/>'));
        $form2.append($('<input type="text" name="empty:string" value=""/>'));

        obj = $form2.serializeJSON({ skipFalsyValuesForFields: [ // using skipFalsyValuesForFields option
          'skipFalsyZero',
          'skipFalsyFalse',
          'skipFalsyNull',
          'zero'
        ]});
        expect(obj["skipFalsyZero"]).toEqual(undefined);  // skip from attr and opt
        expect(obj["skipFalsyFalse"]).toEqual(false); // not skip (attr override)
        expect(obj["skipFalsyNull"]).toEqual(null);  // not skip (attr override)
        expect(obj["skipFalsyEmpty"]).toEqual(undefined); // skip from attr
        expect(obj["skipFalsyFoo"]).toEqual("foo");
        expect(obj["zero"]).toEqual(undefined); // skip from opt
        expect(obj["empty"]).toEqual("");
      });

      it("overrides the option skipFalsyValuesForTypes", function() {
        $form2 = $('<form>');
        $form2.append($('<input type="text" name="skipFalsyZero:number"    data-skip-falsy="true"  value="0"/>'));
        $form2.append($('<input type="text" name="skipFalsyFalse:boolean"  data-skip-falsy="false" value="false"/>'));
        $form2.append($('<input type="text" name="skipFalsyNull:null"      data-skip-falsy="false" value="null"/>'));
        $form2.append($('<input type="text" name="skipFalsyEmpty:string"   data-skip-falsy="true"  value=""/>'));
        $form2.append($('<input type="text" name="skipFalsyFoo:string"     data-skip-falsy="true"  value="foo"/>'));
        $form2.append($('<input type="text" name="zero:number"  value="0"/>'));
        $form2.append($('<input type="text" name="empty:string" value=""/>'));
        $form2.append($('<input type="text" name="null:null"    value="null"/>'));


        obj = $form2.serializeJSON({ skipFalsyValuesForTypes: [ // using skipFalsyValuesForFields option
          'number',
          'boolean',
          'null'
        ]});
        expect(obj["skipFalsyZero"]).toEqual(undefined);  // skip from attr and opt
        expect(obj["skipFalsyFalse"]).toEqual(false); // not skip (attr override)
        expect(obj["skipFalsyNull"]).toEqual(null);  // not skip (attr override)
        expect(obj["skipFalsyEmpty"]).toEqual(undefined); // skip from attr
        expect(obj["skipFalsyFoo"]).toEqual("foo");
        expect(obj["zero"]).toEqual(undefined); // skip from opt
        expect(obj["empty"]).toEqual("");
        expect(obj["null"]).toEqual(undefined); // skip from opt
      });
    });
  });

  // options
  describe('options', function() {
    beforeEach(function() {
      $form = $('<form>');
      $form.append($('<input type="text" name="Numeric 0"     value="0"/>'));
      $form.append($('<input type="text" name="Numeric 1"     value="1"/>'));
      $form.append($('<input type="text" name="Numeric 2.2"   value="2.2"/>'));
      $form.append($('<input type="text" name="Numeric -2.25" value="-2.25"/>'));
      $form.append($('<input type="text" name="Bool true"     value="true"/>'));
      $form.append($('<input type="text" name="Bool false"    value="false"/>'));
      $form.append($('<input type="text" name="Null"          value="null"/>'));
      $form.append($('<input type="text" name="String"        value="text is always string"/>'));
      $form.append($('<input type="text" name="Empty"         value=""/>'));
    });

    describe('defaults (defaultOptions)', function() {
      it("returns strings", function() {
        obj = $form.serializeJSON({}); // empty object should be translated to default options
        expect(obj).toEqual({
          "Numeric 0":     "0",
          "Numeric 1":     "1",
          "Numeric 2.2":   "2.2",
          "Numeric -2.25": "-2.25",
          "Bool true":     "true",
          "Bool false":    "false",
          "Null":          "null",
          "String":        "text is always string",
          "Empty":         ""
        });
      });
    });

    describe('validateOptions', function() {
      it("should raise an error if the option is not one of the valid options", function() {
        expect(function(){ $form.serializeJSON({invalidOption: true}); })
          .toThrow(new Error("serializeJSON ERROR: invalid option 'invalidOption'. Please use one of checkboxUncheckedValue, parseNumbers, parseBooleans, parseNulls, parseAll, parseWithFunction, skipFalsyValuesForTypes, skipFalsyValuesForFields, customTypes, defaultTypes, useIntKeysAsArrayIndex"));
      });
    });

    describe('parseNumbers', function() {
      it("returns numbers for the numeric string values", function() {
        obj = $form.serializeJSON({parseNumbers: true});
        expect(obj).toEqual({
          "Numeric 0":     0,
          "Numeric 1":     1,
          "Numeric 2.2":   2.2,
          "Numeric -2.25": -2.25,
          "Bool true":     "true",
          "Bool false":    "false",
          "Null":          "null",
          "String":        "text is always string",
          "Empty":         ""
        });
      });
    });

    describe('parseBooleans', function() {
      it("returns booleans for the 'true'/'false' values", function() {
        obj = $form.serializeJSON({parseBooleans: true});
        expect(obj).toEqual({
          "Numeric 0":     "0",
          "Numeric 1":     "1",
          "Numeric 2.2":   "2.2",
          "Numeric -2.25": "-2.25",
          "Bool true":     true,
          "Bool false":    false,
          "Null":          "null",
          "String":        "text is always string",
          "Empty":         ""
        });
      });
    });

    describe('parseNulls', function() {
      it("returns null for the 'null' values", function() {
        obj = $form.serializeJSON({parseNulls: true}); // empty object should be translated to default options
        expect(obj).toEqual({
          "Numeric 0":     "0",
          "Numeric 1":     "1",
          "Numeric 2.2":   "2.2",
          "Numeric -2.25": "-2.25",
          "Bool true":     "true",
          "Bool false":    "false",
          "Null":          null,
          "String":        "text is always string",
          "Empty":         ""
        });
      });
    });

    describe('parseAll', function() {
      it("parses all possible values", function() {
        obj = $form.serializeJSON({parseAll: true});
        expect(obj).toEqual({
          "Numeric 0":     0,
          "Numeric 1":     1,
          "Numeric 2.2":   2.2,
          "Numeric -2.25": -2.25,
          "Bool true":     true,
          "Bool false":    false,
          "Null":          null,
          "String":        "text is always string",
          "Empty":         ""
        });
      });
    });

    describe('parseWithFunction custom parser', function() {
      it("uses the passed in function to parse values", function() {
        var myParser = function(val) { return val === "true" ? 1 : 0};
        obj = $form.serializeJSON({parseWithFunction: myParser});
        expect(obj).toEqual({
          "Numeric 0":     0,
          "Numeric 1":     0,
          "Numeric 2.2":   0,
          "Numeric -2.25": 0,
          "Bool true":     1,
          "Bool false":    0,
          "Null":          0,
          "String":        0,
          "Empty":         0
        });
      });

      it("can be combined with other parse options", function() {
        var myParser = function(val) { return typeof(val) === "number" ? 1 : 0};
        obj = $form.serializeJSON({parseNumbers: true, parseWithFunction: myParser});
        expect(obj).toEqual({
          "Numeric 0":     1,
          "Numeric 1":     1,
          "Numeric 2.2":   1,
          "Numeric -2.25": 1,
          "Bool true":     0,
          "Bool false":    0,
          "Null":          0,
          "String":        0,
          "Empty":         0
        });
      });
    });

    describe('skipFalsyValuesForFields', function() {
      it("skips serialization of falsy values but only on inputs with given names", function() {
        obj = $form.serializeJSON({skipFalsyValuesForFields: ['Empty', 'Null', 'Numeric 0', 'String']});
        expect(obj).toEqual({
          "Numeric 0":     "0", // "0" as :string is not falsy
          "Numeric 1":     "1",
          "Numeric 2.2":   "2.2",
          "Numeric -2.25": "-2.25",
          "Bool true":     "true",
          "Bool false":    "false",
          "Null":          "null", // "null" as :string is not falsy
          "String":        "text is always string"
          // "Empty" skip
        });
      });

      it("checks on values after they were parsed by the given types", function() {
        // with parseAll = true, the values are typed
        obj = $form.serializeJSON({parseAll: true, skipFalsyValuesForFields: ['Empty', 'Null', 'Numeric 0', 'String']});
        expect(obj).toEqual({
          // "Numeric 0" skip
          "Numeric 1":     1,
          "Numeric 2.2":   2.2,
          "Numeric -2.25": -2.25,
          "Bool true":     true,
          "Bool false":    false, // not skip because was not included in list of names
          "String":        "text is always string"
          // "Null" skip
          // "Empty" skip
        });
      });
    });

    describe('skipFalsyValuesForTypes', function() {
      it("skips serialization of falsy values for on inputs of the given types", function() {
        var $form2 = $('<form>');
        $form2.append($('<input type="text" name="Num0:number"         value="0"/>'));
        $form2.append($('<input type="text" name="Num1:number"         value="1"/>'));
        $form2.append($('<input type="text" name="NaN:number"          value="wololoo"/>'));
        $form2.append($('<input type="text" name="Num0attr"            value="0" data-value-type="number"/>'));
        $form2.append($('<input type="text" name="Num1attr"            value="1" data-value-type="number"/>'));
        $form2.append($('<input type="text" name="Bool true:boolean"   value="true"/>'));
        $form2.append($('<input type="text" name="Bool false:boolean"  value="false"/>'));
        $form2.append($('<input type="text" name="Text:string"         value="text is always string"/>'));
        $form2.append($('<input type="text" name="Empty String:string" value=""/>'));
        $form2.append($('<input type="text" name="Empty Implicit"      value=""/>')); // :string type is implicit
        $form2.append($('<input type="text" name="Array:array"         value="[1, 2]"/>'));
        $form2.append($('<input type="text" name="Empty Array:array"   value="[]"/>'));
        $form2.append($('<input type="text" name="Null:null"           value="null"/>'));

        obj = $form2.serializeJSON({skipFalsyValuesForTypes: ["number", "boolean", "string", "array", "null"]});
        expect(obj["Num0"]).toEqual(undefined); // skip
        expect(obj["Num1"]).toEqual(1);
        expect(obj["NaN"]).toEqual(undefined); // skip
        expect(obj["Num0attr"]).toEqual(undefined); // skip
        expect(obj["Num1attr"]).toEqual(1);
        expect(obj["Bool true"]).toEqual(true);
        expect(obj["Bool false"]).toEqual(undefined); // skip
        expect(obj["Text"]).toEqual("text is always string");
        expect(obj["Empty String"]).toEqual(undefined);
        expect(obj["Empty Implicit"]).toEqual(undefined);
        expect(obj["Array"]).toEqual([1, 2]);
        expect(obj["Empty Array"]).toEqual([]); // Not skip! empty arrays are not falsy
        expect(obj["Null"]).toEqual(undefined); // skip


        obj = $form2.serializeJSON({skipFalsyValuesForTypes: ["number"]}); // skip only falsy numbers
        expect(obj["Num0"]).toEqual(undefined); // skip
        expect(obj["Num1"]).toEqual(1);
        expect(obj["NaN"]).toEqual(undefined); // skip
        expect(obj["Num0attr"]).toEqual(undefined); // skip
        expect(obj["Num1attr"]).toEqual(1);
        expect(obj["Bool true"]).toEqual(true);
        expect(obj["Bool false"]).toEqual(false);
        expect(obj["Text"]).toEqual("text is always string");
        expect(obj["Empty String"]).toEqual("");
        expect(obj["Empty Implicit"]).toEqual("");
        expect(obj["Array"]).toEqual([1, 2]);
        expect(obj["Empty Array"]).toEqual([]);
        expect(obj["Null"]).toEqual(null);
      });
    });


    describe('checkboxUncheckedValue', function() {
      it('uses that value for unchecked checkboxes', function() {
        $form = $('<form>');
        $form.append($('<input type="checkbox" name="check1" value="yes"/>'));
        $form.append($('<input type="checkbox" name="check2" value="yes"/>'));
        $form.append($('<input type="checkbox" name="check3" value="yes" checked/>'));

        obj = $form.serializeJSON({checkboxUncheckedValue: 'NOPE'});
        expect(obj).toEqual({check1: 'NOPE', check2: 'NOPE', check3: 'yes'});
      });

      it('is overriden by data-unchecked-value attribute', function() {
        $form = $('<form>');
        $form.append($('<input type="checkbox" name="check1" value="yes"/>'));
        $form.append($('<input type="checkbox" name="check2" value="yes" data-unchecked-value="OVERRIDE"/>'));
        $form.append($('<input type="checkbox" name="check3" value="yes" checked/>'));

        obj = $form.serializeJSON({checkboxUncheckedValue: 'NOPE'});
        expect(obj).toEqual({check1: 'NOPE', check2: 'OVERRIDE', check3: 'yes'});
      });

      it('is parsed by parse options', function() {
        $form = $('<form>');
        $form.append($('<input type="checkbox" name="check1" value="true"/>'));
        $form.append($('<input type="checkbox" name="check2" value="true" data-unchecked-value="0"/>'));
        $form.append($('<input type="checkbox" name="check3" value="true" checked/>'));

        obj = $form.serializeJSON({checkboxUncheckedValue: 'false', parseBooleans: true, parseNumbers: true});
        expect(obj).toEqual({check1: false, check2: 0, check3: true});
      });

      it('is parsed by custom parseWithFunction', function() {
        $form = $('<form>');
        $form.append($('<input type="checkbox" name="check1" value="yes"/>'));
        $form.append($('<input type="checkbox" name="check2" value="yes" data-unchecked-value="NOPE"/>'));
        $form.append($('<input type="checkbox" name="check3" value="yes" checked/>'));

        var parser = function(val) { return val == 'yes' };
        obj = $form.serializeJSON({checkboxUncheckedValue: 'no', parseWithFunction: parser});
        expect(obj).toEqual({check1: false, check2: false, check3: true});
      });

      if ($.fn.jquery) { // not supported on Zepto
        it('works on multiple forms and inputs', function() {
          var $form1, $form2, $els;
          $form1 = $('<form>');
          $form1.append($('<input type="text"     name="form1[title]"  value="form1"/>'));
          $form1.append($('<input type="checkbox" name="form1[check1]" value="true"/>'));
          $form1.append($('<input type="checkbox" name="form1[check2]" value="true" data-unchecked-value="NOPE"/>'));
          $form2 = $('<form>');
          $form1.append($('<input type="text"     name="form2[title]"  value="form2"/>'));
          $form2.append($('<input type="checkbox" name="form2[check1]" value="true" checked="checked"/>'));
          $form2.append($('<input type="checkbox" name="form2[check2]" value="true" />'));
          $inputs = $()
                  .add($('<input type="text"      name="inputs[title]"  value="inputs"/>'))
                  .add($('<input type="checkbox"  name="inputs[check1]" value="true" checked="checked"/>'))
                  .add($('<input type="checkbox"  name="inputs[check2]" value="true"/>'))
                  .add($('<input type="checkbox"  name="inputs[check3]" value="true" data-unchecked-value="NOPE"/>'));
          $els = $form1.add($form2).add($inputs);

          obj = $els.serializeJSON({checkboxUncheckedValue: 'false'});
          expect(obj).toEqual({
            form1: {
              title: 'form1',
              check1: 'false',
              check2: 'NOPE',
            },
            form2: {
              title: 'form2',
              check1: 'true',
              check2: 'false'
            },
            inputs: {
              title: 'inputs',
              check1: 'true',
              check2: 'false',
              check3: 'NOPE'
            }
          })
        });
      }

      it('works on a list of checkboxes', function() {
        $form = $('<form>' +
          '<label class="checkbox-inline">' +
          '  <input type="checkbox" name="flags[]" value="input1"> Input 1' +
          '</label>' +
          '<label class="checkbox-inline">' +
          '  <input type="checkbox" name="flags[]" value="input2"> Input 2' +
          '</label>' +
          '</form>');
        obj = $form.serializeJSON({checkboxUncheckedValue: 'false'});
        expect(obj).toEqual({
          'flags': ['false', 'false']
        });

        $form.find('input[value="input1"]').prop('checked', true);
        obj = $form.serializeJSON({checkboxUncheckedValue: 'false'});
        expect(obj).toEqual({
          'flags': ['input1', 'false']
        });
      });

      it('works on a nested list of checkboxes', function() {
        $form = $('<form>');
        $form.append($('<input type="text"     name="form[title]"   value="list of checkboxes"/>'));
        $form.append($('<input type="checkbox" name="form[check][]" value="true" checked/>'));
        $form.append($('<input type="checkbox" name="form[check][]" value="true"/>'));
        $form.append($('<input type="checkbox" name="form[check][]" value="true" data-unchecked-value="NOPE"/>'));
        obj = $form.serializeJSON({checkboxUncheckedValue: 'false'});
        expect(obj).toEqual({
          form: {
            title: 'list of checkboxes',
            check: ['true', 'false', 'NOPE']
          }
        });

        // also with parse options
        obj = $form.serializeJSON({checkboxUncheckedValue: 'false', parseBooleans: true});
        expect(obj).toEqual({
          form: {
            title: 'list of checkboxes',
            check: [true, false, 'NOPE']
          }
        });
      });

      it('does not work on a nested list of objects', function() {
        $form = $('<form>');
        $form.append($('<input type="checkbox" name="answers[][correct]:boolean" value="true" data-unchecked-value="false">'));
        $form.append($('<input type="text"     name="answers[][text]" value="Blue">'));

        $form.append($('<input type="checkbox" name="answers[][correct]:boolean" value="true" data-unchecked-value="false">'));
        $form.append($('<input type="text"     name="answers[][text]" value="Green">'));

        expect(function(){$form.serializeJSON()}).toThrow(); // it throws a descriptive error for the user
      });

      it('does not serialize disabled checkboxes', function() {
        $form = $('<form>');
        $form.append($('<input type="checkbox" name="checkDisabled1" value="true" disabled/>'));
        $form.append($('<input type="checkbox" name="checkDisabled2" value="true" disabled data-unchecked-value="NOPE"/>'));
        obj = $form.serializeJSON({checkboxUncheckedValue: 'false'});
        expect(obj).toEqual({});
      });
    });

    describe('useIntKeysAsArrayIndex', function() {
      it("uses int keys as array indexes instead of object properties", function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="foo[0]" value="0"/>'));
        $form.append($('<input type="text" name="foo[1]" value="1"/>'));
        $form.append($('<input type="text" name="foo[5]" value="5"/>'));

        obj = $form.serializeJSON({useIntKeysAsArrayIndex: false}); // default
        expect(obj).toEqual({"foo": {'0': '0', '1': '1', '5': '5'}});

        obj = $form.serializeJSON({useIntKeysAsArrayIndex: true}); // with option useIntKeysAsArrayIndex true
        expect(obj).toEqual({"foo": ['0', '1', undefined, undefined, undefined, '5']});

        obj = $form.serializeJSON({useIntKeysAsArrayIndex: true, parseNumbers: true}); // same but also parsing numbers
        expect(obj).toEqual({"foo": [0, 1, undefined, undefined, undefined, 5]});
      });

      it("doesnt get confused by attribute names that are similar to integers, but not valid array indexes", function() { // only integers are mapped to an array
        $form = $('<form>');
        $form.append($('<input type="text"  name="drinks[1st]" value="coffee"/>'));
        $form.append($('<input type="text"  name="drinks[2nd]" value="beer"/>'));

        obj = $form.serializeJSON({useIntKeysAsArrayIndex: true});
        expect(obj).toEqual({
          drinks: {
            '1st': "coffee",
            '2nd': "beer"
          }
        });
      });

      it("regresion for github issue #69", function() {
        $form = $('<form>');
        $form.append($('<input name="array[0][value]" value="value">'));
        obj = $form.serializeJSON({useIntKeysAsArrayIndex: true});
        expect(obj).toEqual({"array": [{"value": "value"}]});
      });
    });

    describe('customTypes', function() {
      it("serializes value according to custom function without disturbing default types", function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="foo:alwaysBoo"    value="0"/>'));
        $form.append($('<input type="text" name="notype"           value="default type is :string"/>'));
        $form.append($('<input type="text" name="string:string"    value=":string type overrides parsing options"/>'));
        $form.append($('<input type="text" name="excludes:skip"    value="Use :skip to not include this field in the result"/>'));

        $form.append($('<input type="text" name="number[1]:number"           value="1"/>'));
        $form.append($('<input type="text" name="number[1.1]:number"         value="1.1"/>'));
        $form.append($('<input type="text" name="number[other stuff]:number" value="other stuff"/>'));

        $form.append($('<input type="text" name="boolean[true]:boolean"      value="true"/>'));
        $form.append($('<input type="text" name="boolean[false]:boolean"     value="false"/>'));
        $form.append($('<input type="text" name="boolean[0]:boolean"         value="0"/>'));

        $form.append($('<input type="text" name="null[null]:null"            value="null"/>'));
        $form.append($('<input type="text" name="null[other stuff]:null"     value="other stuff"/>'));

        $form.append($('<input type="text" name="auto[string]:auto"          value="text with stuff"/>'));
        $form.append($('<input type="text" name="auto[0]:auto"               value="0"/>'));
        $form.append($('<input type="text" name="auto[1]:auto"               value="1"/>'));
        $form.append($('<input type="text" name="auto[true]:auto"            value="true"/>'));
        $form.append($('<input type="text" name="auto[false]:auto"           value="false"/>'));
        $form.append($('<input type="text" name="auto[null]:auto"            value="null"/>'));
        $form.append($('<input type="text" name="auto[list]:auto"            value="[1, 2, 3]"/>'));

        $form.append($('<input type="text" name="array[empty]:array"         value="[]"/>'));
        $form.append($('<input type="text" name="array[not empty]:array"     value="[1, 2, 3]"/>'));

        $form.append($('<input type="text" name="object[empty]:object"       value="{}"/>'));
        $form.append($('<input type="text" name="object[not empty]:object"   value=\'{"my": "stuff"}\'/>'));

        obj = $form.serializeJSON({
          customTypes: {
            alwaysBoo: function() { return "Boo" }
          }
        });

        expect(obj).toEqual({
          "foo": "Boo",
          "notype": "default type is :string",
          "string": ":string type overrides parsing options",
          // :skip type removes the field from the output
          "number": {
            "1": 1,
            "1.1": 1.1,
            "other stuff": NaN, // <-- Other stuff parses as NaN (Not a Number)
          },
          "boolean": {
            "true": true,
            "false": false,
            "0": false, // <-- "false", "null", "undefined", "", "0" parse as false
          },
          "null": {
            "null": null, // <-- "false", "null", "undefined", "", "0" parse as null
            "other stuff": "other stuff"
          },
          "auto": { // works as the parseAll option
            "string": "text with stuff",
            "0": 0,         // <-- parsed as number
            "1": 1,         // <-- parsed as number
            "true": true,   // <-- parsed as boolean
            "false": false, // <-- parsed as boolean
            "null": null,   // <-- parsed as null
            "list": "[1, 2, 3]" // <-- array and object types are not auto-parsed
          },
          "array": { // <-- works using JSON.parse
            "empty": [],
            "not empty": [1,2,3]
          },
          "object": { // <-- works using JSON.parse
            "empty": {},
            "not empty": {"my": "stuff"}
          }
        });
      });

      it("overrides defaultTypes", function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="incremented:number" value="0"/>'));
        obj = $form.serializeJSON({
          customTypes: {
            number: function(str) { return Number(str) + 1 }
          }
        });
        expect(obj).toEqual({ "incremented": 1 });
      });

      it("overrides defaultTypes even if they are re-defined", function() {
        $form = $('<form>');
        $form.append($('<input type="text" name="num:number" value="0"/>'));

        obj = $form.serializeJSON({
          defaultTypes: {
            number: function(str) { return 1 }
          }
        });
        expect(obj).toEqual({ "num": 1 });

        obj = $form.serializeJSON({
          defaultTypes: {
            number: function(str) { return 1 }
          },
          customTypes: {
            number: function(str) { return 22 }
          }
        });
        expect(obj).toEqual({ "num": 22 });
      });
    });

    describe('with modified defaults', function() {
      var defaults = $.serializeJSON.defaultOptions;
      afterEach(function() {
        $.serializeJSON.defaultOptions = defaults; // restore defaults
      });

      it('uses those options by default', function() {
        $.serializeJSON.defaultOptions = {parseBooleans: true, parseNulls: true};
        obj = $form.serializeJSON({});
        expect(obj).toEqual({
          "Numeric 0":     "0",
          "Numeric 1":     "1",
          "Numeric 2.2":   "2.2",
          "Numeric -2.25": "-2.25",
          "Bool true":     true,
          "Bool false":    false,
          "Null":          null,
          "String":        "text is always string",
          "Empty":         ""
        });

        $.serializeJSON.defaultOptions = {parseNumbers: true, parseNulls: true};
        obj = $form.serializeJSON({});
        expect(obj).toEqual({
          "Numeric 0":     0,
          "Numeric 1":     1,
          "Numeric 2.2":   2.2,
          "Numeric -2.25": -2.25,
          "Bool true":     "true",
          "Bool false":    "false",
          "Null":          null,
          "String":        "text is always string",
          "Empty":         ""
        });
      });

      it('merges options with defaults', function() {
        var myParser = function(val) { return typeof(val) === "number" ? 1 : 0};
        $.serializeJSON.defaultOptions = {parseWithFunction: myParser};
        obj = $form.serializeJSON({parseNumbers: true});
        expect(obj).toEqual({
          "Numeric 0":     1,
          "Numeric 1":     1,
          "Numeric 2.2":   1,
          "Numeric -2.25": 1,
          "Bool true":     0,
          "Bool false":    0,
          "Null":          0,
          "String":        0,
          "Empty":         0
        });
      });

      it('can be overriden with different options', function() {
        $.serializeJSON.defaultOptions = {parseBooleans: true, parseNulls: true};
        obj = $form.serializeJSON({parseBooleans: false}); // override default parseBooleans: true
        expect(obj).toEqual({
          "Numeric 0":     "0",
          "Numeric 1":     "1",
          "Numeric 2.2":   "2.2",
          "Numeric -2.25": "-2.25",
          "Bool true":     "true",
          "Bool false":    "false",
          "Null":          null,
          "String":        "text is always string",
          "Empty":         ""
        });
      });

      it('parseAll will override all other parse options', function() {
        $.serializeJSON.defaultOptions = {parseNumbers: true, parseBooleans: false, parseNulls: false, parseAll: true};
        obj = $form.serializeJSON({parseNumbers: false}); // but default parseAll is true
        expect(obj).toEqual({
          "Numeric 0":     0,
          "Numeric 1":     1,
          "Numeric 2.2":   2.2,
          "Numeric -2.25": -2.25,
          "Bool true":     true,
          "Bool false":    false,
          "Null":          null,
          "String":        "text is always string",
          "Empty":         ""
        });

        obj = $form.serializeJSON({parseAll: false}); // but default parseNumbers is true
        expect(obj).toEqual({
          "Numeric 0":     0,
          "Numeric 1":     1,
          "Numeric 2.2":   2.2,
          "Numeric -2.25": -2.25,
          "Bool true":     "true",
          "Bool false":    "false",
          "Null":          "null",
          "String":        "text is always string",
          "Empty":         ""
        });
      });

      it('allows to set default for checkboxUncheckedValue', function() {
        var $checkForm = $('<form>');
        $checkForm.append($('<input type="checkbox" name="check1" value="true" checked/>'));
        $checkForm.append($('<input type="checkbox" name="check2" value="true"/>'));
        $checkForm.append($('<input type="checkbox" name="check3" value="true" data-unchecked-value="unchecked_from_data_attr"/>'));

        $.serializeJSON.defaultOptions = {checkboxUncheckedValue: 'unchecked_from_defaults'};
        obj = $checkForm.serializeJSON(); // with defaults
        expect(obj).toEqual({
          'check1': 'true',
          'check2': 'unchecked_from_defaults',
          'check3': 'unchecked_from_data_attr'
        });

        obj = $checkForm.serializeJSON({checkboxUncheckedValue: 'unchecked_from_option'}); // override defaults
        expect(obj).toEqual({
          'check1': 'true',
          'check2': 'unchecked_from_option',
          'check3': 'unchecked_from_data_attr'
        });
      });
    });
  });
});

// extractTypeAndNameWithNoType
describe("$.serializeJSON.extractTypeAndNameWithNoType", function() {
  var extract = $.serializeJSON.extractTypeAndNameWithNoType;
  it("returns an object with type and nameWithNoType properties form the name with :type colon notation", function() {
    expect(extract('foo')).toEqual({nameWithNoType: 'foo', type: null})
    expect(extract('foo:boolean')).toEqual({nameWithNoType: 'foo', type: 'boolean'})
    expect(extract('foo[bar]:null')).toEqual({nameWithNoType: 'foo[bar]', type: 'null'})
  });
});

// splitInputNameIntoKeysArray
describe("$.serializeJSON.splitInputNameIntoKeysArray", function() {
  var split = $.serializeJSON.splitInputNameIntoKeysArray;
  it("accepts a simple name", function() {
    expect(split('foo')).toEqual(['foo']);
  });
  it("accepts a name wrapped in brackets", function() {
    expect(split('[foo]')).toEqual(['foo']);
  });
  it("accepts names separated by brackets", function() {
    expect(split('foo[inn][bar]')).toEqual(['foo', 'inn', 'bar']);
    expect(split('foo[inn][bar][0]')).toEqual(['foo', 'inn', 'bar', '0']);
  });
  it("accepts empty brakets as empty strings", function() {
    expect(split('arr[][bar]')).toEqual(['arr', '', 'bar']);
    expect(split('arr[][][bar]')).toEqual(['arr', '', '', 'bar']);
    expect(split('arr[][bar][]')).toEqual(['arr', '', 'bar', '']);
  });
  it("accepts nested brackets", function() {
    expect(split('foo[inn[bar]]')).toEqual(['foo', 'inn', 'bar']);
    expect(split('foo[inn[bar[0]]]')).toEqual(['foo', 'inn', 'bar', '0']);
    expect(split('[foo[inn[bar[0]]]]')).toEqual(['foo', 'inn', 'bar', '0']);
    expect(split('foo[arr[]]')).toEqual(['foo', 'arr', '']);
    expect(split('foo[bar[arr[]]]')).toEqual(['foo', 'bar', 'arr', '']);
  });
});

// isValidArrayIndex
describe("$.serializeJSON.isValidArrayIndex", function() {
  var validIndex = $.serializeJSON.isValidArrayIndex;
  it("accepts positive integers", function() {
    expect(validIndex(0)).toBeTruthy();
    expect(validIndex(1)).toBeTruthy();
    expect(validIndex(222)).toBeTruthy();
    expect(validIndex('0')).toBeTruthy();
    expect(validIndex('1')).toBeTruthy();
    expect(validIndex('222')).toBeTruthy();
  });
  it("rejects negative integers", function() {
    expect(validIndex(-1)).toBeFalsy();
    expect(validIndex(-22)).toBeFalsy();
  });
  it("rejects strings, objects and arrays", function() {
    expect(validIndex('')).toBeFalsy();
    expect(validIndex('foo')).toBeFalsy();
    expect(validIndex({'foo': 'var'})).toBeFalsy();
    expect(validIndex([0,1,2])).toBeFalsy();
  });
});

// deepSet
// used to assign nested keys like "address[state][abbr]" to an object
describe("$.serializeJSON.deepSet", function () {
  var deepSet = $.serializeJSON.deepSet;
  var arr, obj, v, v2;

  beforeEach(function () {
    obj = {};
    arr = [];
    v = 'v';
    v2 = 'v2';
  });

  it("simple attr ['foo']", function () {
    deepSet(obj, ['foo'], v);
    expect(obj).toEqual({foo: v});
  });

  it("simple attr ['foo'] twice should set the last value", function () {
    deepSet(obj, ['foo'], v);
    deepSet(obj, ['foo'], v2);
    expect(obj).toEqual({foo: v2});
  });

  it("nested attr ['inn', 'foo']", function () {
    deepSet(obj, ['inn', 'foo'], v);
    expect(obj).toEqual({inn: {foo: v}});
  });

  it("nested attr ['inn', 'foo'] twice should set the last value", function () {
    deepSet(obj, ['inn', 'foo'], v);
    deepSet(obj, ['inn', 'foo'], v2);
    expect(obj).toEqual({inn: {foo: v2}});
  });

  it("multiple assign attr ['foo'] and ['inn', 'foo']", function () {
    deepSet(obj, ['foo'], v);
    deepSet(obj, ['inn', 'foo'], v);
    expect(obj).toEqual({foo: v, inn: {foo: v}});
  });

  it("very nested attr ['inn', 'inn', 'inn', 'foo']", function () {
    deepSet(obj, ['inn', 'inn', 'inn', 'foo'], v);
    expect(obj).toEqual({inn: {inn: {inn: {foo: v}}}});
  });

  it("array push with empty index, if repeat same object element key then it creates a new element", function () {
    deepSet(arr, [''], v);        //=> arr === [v]
    deepSet(arr, ['', 'foo'], v); //=> arr === [v, {foo: v}]
    deepSet(arr, ['', 'bar'], v); //=> arr === [v, {foo: v, bar: v}]
    deepSet(arr, ['', 'bar'], v); //=> arr === [v, {foo: v, bar: v}, {bar: v}]
    expect(arr).toEqual([v, {foo: v, bar: v}, {bar: v}]);
  });

  it("array push with empty index and empty value, also creates a new element", function () {
    deepSet(arr, ['', 'foo'], ''); //=> arr === [{foo: ''}]
    deepSet(arr, ['', 'foo'], ''); //=> arr === [{foo: ''}, {foo: ''}, {foo: v}]
    deepSet(arr, ['', 'foo'], v);  //=> arr === [{foo: ''}, {foo: ''}, {foo: v}]
    deepSet(arr, ['', 'foo'], ''); //=> arr === [{foo: ''}, {foo: ''}, {foo: v}, {foo: ''}]
    expect(arr).toEqual([{foo: ''}, {foo: ''}, {foo: v}, {foo: ''}]);
  });

  it("array assign with empty index should push the element", function () {
    deepSet(arr, [''], 1);
    deepSet(arr, [''], 2);
    deepSet(arr, [''], 3);
    expect(arr).toEqual([1,2,3]);
  });

  it("nested array assign with empty index should push the element", function () {
    deepSet(obj, ['arr', ''], 1);
    deepSet(obj, ['arr', ''], 2);
    deepSet(obj, ['arr', ''], 3);
    expect(obj).toEqual({arr: [1,2,3]});
  });

  it("nested arrays with empty indexes should push the elements to the most deep array", function () {
    deepSet(arr, ['', '', ''], 1);
    deepSet(arr, ['', '', ''], 2);
    deepSet(arr, ['', '', ''], 3);
    expect(arr).toEqual([[[1, 2, 3]]]);
  });

  describe('with useIntKeysAsArrayIndex option', function(){
    var intIndx = {useIntKeysAsArrayIndex: true}

    it("simple array ['0']", function () {
      arr = [];
      deepSet(arr, ['0'], v);
      expect(arr).toEqual([v]); // still sets the value in the array because the 1st argument is an array

      arr = [];
      deepSet(arr, ['0'], v, intIndx);
      expect(arr).toEqual([v]);
    });

    it("nested simple array ['arr', '0']", function () {
      obj = {};
      deepSet(obj, ['arr', '0'], v);
      expect(obj).toEqual({'arr': {'0': v}});

      obj = {};
      deepSet(obj, ['arr', '0'], v, intIndx);
      expect(obj).toEqual({'arr': [v]});
    });

    it("nested simple array multiple values", function () {
      obj = {};
      deepSet(obj, ['arr', '1'], v2);
      deepSet(obj, ['arr', '0'], v);
      expect(obj).toEqual({'arr': {'0': v, '1': v2}});

      obj = {};
      deepSet(obj, ['arr', '1'], v2, intIndx);
      deepSet(obj, ['arr', '0'], v, intIndx);
      expect(obj).toEqual({'arr': [v, v2]});
    });

    it("nested arrays with indexes should create a matrix", function () {
      arr = [];
      deepSet(arr, ['0', '0', '0'], 1);
      deepSet(arr, ['0', '0', '1'], 2);
      deepSet(arr, ['0', '1', '0'], 3);
      deepSet(arr, ['0', '1', '1'], 4);
      deepSet(arr, ['1', '0', '0'], 5);
      deepSet(arr, ['1', '0', '1'], 6);
      deepSet(arr, ['1', '1', '0'], 7);
      deepSet(arr, ['1', '1', '1'], 8);
      expect(arr).toEqual([{ '0': {'0': 1, '1': 2}, '1': {'0': 3, '1': 4}}, {'0': {'0': 5, '1': 6}, '1': {'0': 7, '1': 8}}]);

      arr = [];
      deepSet(arr, ['0', '0', '0'], 1, intIndx);
      deepSet(arr, ['0', '0', '1'], 2, intIndx);
      deepSet(arr, ['0', '1', '0'], 3, intIndx);
      deepSet(arr, ['0', '1', '1'], 4, intIndx);
      deepSet(arr, ['1', '0', '0'], 5, intIndx);
      deepSet(arr, ['1', '0', '1'], 6, intIndx);
      deepSet(arr, ['1', '1', '0'], 7, intIndx);
      deepSet(arr, ['1', '1', '1'], 8, intIndx);
      expect(arr).toEqual([[[1, 2], [3, 4]], [[5, 6], [7, 8]]]);
    });

    it("nested object as array element ['arr', '0', 'foo']", function () {
      obj = {};
      deepSet(obj, ['arr', '0', 'foo'], v);
      expect(obj).toEqual({arr: {'0': {foo: v}}});

      obj = {};
      deepSet(obj, ['arr', '0', 'foo'], v, intIndx);
      expect(obj).toEqual({arr: [{foo: v}]});
    });

    it("array of objects", function (){
      obj = {};
      deepSet(obj, ['arr', '0', 'foo'], v);
      deepSet(obj, ['arr', '0', 'bar'], v);
      deepSet(obj, ['arr', '1', 'foo'], v2);
      deepSet(obj, ['arr', '1', 'bar'], v2);
      expect(obj).toEqual({'arr': {'0': {foo: v, bar: v}, '1': {foo: v2, bar: v2}}});

      obj = {};
      deepSet(obj, ['arr', '0', 'foo'], v, intIndx);
      deepSet(obj, ['arr', '0', 'bar'], v, intIndx);
      deepSet(obj, ['arr', '1', 'foo'], v2, intIndx);
      deepSet(obj, ['arr', '1', 'bar'], v2, intIndx);
      expect(obj).toEqual({'arr': [{foo: v, bar: v}, {foo: v2, bar: v2}]});
    });

    it("nested arrays mixing empty indexes with numeric indexes should push when using empty but assign when using numeric", function () {
      obj = {};
      deepSet(obj, ['arr', '', '0', ''], 1);
      deepSet(obj, ['arr', '', '1', ''], 2);
      deepSet(obj, ['arr', '', '0', ''], 3);
      deepSet(obj, ['arr', '', '1', ''], 4);
      expect(obj).toEqual({'arr': [{'0': [1, 3], '1': [2, 4]}]});

      obj = {};
      deepSet(obj, ['arr', '', '0', ''], 1, intIndx);
      deepSet(obj, ['arr', '', '1', ''], 2, intIndx);
      deepSet(obj, ['arr', '', '0', ''], 3, intIndx);
      deepSet(obj, ['arr', '', '1', ''], 4, intIndx);
      expect(obj).toEqual({'arr': [[[1, 3], [2, 4]]]});
    });

    it("should set all different nested values", function () {
      deepSet(obj, ['foo'], v, intIndx);
      deepSet(obj, ['inn', 'foo'], v, intIndx);
      deepSet(obj, ['inn', 'arr', '0'], v, intIndx);
      deepSet(obj, ['inn', 'arr', '1'], v2, intIndx);
      deepSet(obj, ['inn', 'arr', '2', 'foo'], v, intIndx);
      deepSet(obj, ['inn', 'arr', '2', 'bar'], v), intIndx;
      deepSet(obj, ['inn', 'arr', ''], v, intIndx);
      deepSet(obj, ['inn', 'arr', ''], v2, intIndx);
      deepSet(obj, ['inn', 'arr', '', 'foo'], v2, intIndx);
      deepSet(obj, ['inn', 'arr', '', 'bar'], v2, intIndx);
      deepSet(obj, ['inn', 'arr', '2', 'inn', 'foo'], v, intIndx);
      expect(obj).toEqual({foo: v, inn: {foo: v, arr: [v, v2, {foo: v, bar: v, inn: {foo: v}}, v, v2, {foo: v2, bar: v2}]}})
    });
  });
});
