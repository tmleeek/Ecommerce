/*!
 * GMaps.js
 * http://reinos.nl/expressionengine/gmaps/overview
 *
 * Copyright 2016, Rein de Vries
 * Released under the  License.
 */

// Avoid `console` errors in browsers that lack a console.
(function() {
    var method;
    var noop = function () {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());
//     Underscore.js 1.4.2
//     http://underscorejs.org
//     (c) 2009-2012 Jeremy Ashkenas, DocumentCloud Inc.
//     Underscore may be freely distributed under the MIT license.

(function() {

  // Baseline setup
  // --------------

  // Establish the root object, `window` in the browser, or `global` on the server.
  var root = this;

  // Save the previous value of the `_` variable.
  var previousUnderscore = root._;

  // Establish the object that gets returned to break out of a loop iteration.
  var breaker = {};

  // Save bytes in the minified (but not gzipped) version:
  var ArrayProto = Array.prototype, ObjProto = Object.prototype, FuncProto = Function.prototype;

  // Create quick reference variables for speed access to core prototypes.
  var push             = ArrayProto.push,
      slice            = ArrayProto.slice,
      concat           = ArrayProto.concat,
      unshift          = ArrayProto.unshift,
      toString         = ObjProto.toString,
      hasOwnProperty   = ObjProto.hasOwnProperty;

  // All **ECMAScript 5** native function implementations that we hope to use
  // are declared here.
  var
    nativeForEach      = ArrayProto.forEach,
    nativeMap          = ArrayProto.map,
    nativeReduce       = ArrayProto.reduce,
    nativeReduceRight  = ArrayProto.reduceRight,
    nativeFilter       = ArrayProto.filter,
    nativeEvery        = ArrayProto.every,
    nativeSome         = ArrayProto.some,
    nativeIndexOf      = ArrayProto.indexOf,
    nativeLastIndexOf  = ArrayProto.lastIndexOf,
    nativeIsArray      = Array.isArray,
    nativeKeys         = Object.keys,
    nativeBind         = FuncProto.bind;

  // Create a safe reference to the Underscore object for use below.
  var _ = function(obj) {
    if (obj instanceof _) return obj;
    if (!(this instanceof _)) return new _(obj);
    this._wrapped = obj;
  };

  // Export the Underscore object for **Node.js**, with
  // backwards-compatibility for the old `require()` API. If we're in
  // the browser, add `_` as a global object via a string identifier,
  // for Closure Compiler "advanced" mode.
  if (typeof exports !== 'undefined') {
    if (typeof module !== 'undefined' && module.exports) {
      exports = module.exports = _;
    }
    exports._ = _;
  } else {
    root['_'] = _;
  }

  // Current version.
  _.VERSION = '1.4.2';

  // Collection Functions
  // --------------------

  // The cornerstone, an `each` implementation, aka `forEach`.
  // Handles objects with the built-in `forEach`, arrays, and raw objects.
  // Delegates to **ECMAScript 5**'s native `forEach` if available.
  var each = _.each = _.forEach = function(obj, iterator, context) {
    if (obj == null) return;
    if (nativeForEach && obj.forEach === nativeForEach) {
      obj.forEach(iterator, context);
    } else if (obj.length === +obj.length) {
      for (var i = 0, l = obj.length; i < l; i++) {
        if (iterator.call(context, obj[i], i, obj) === breaker) return;
      }
    } else {
      for (var key in obj) {
        if (_.has(obj, key)) {
          if (iterator.call(context, obj[key], key, obj) === breaker) return;
        }
      }
    }
  };

  // Return the results of applying the iterator to each element.
  // Delegates to **ECMAScript 5**'s native `map` if available.
  _.map = _.collect = function(obj, iterator, context) {
    var results = [];
    if (obj == null) return results;
    if (nativeMap && obj.map === nativeMap) return obj.map(iterator, context);
    each(obj, function(value, index, list) {
      results[results.length] = iterator.call(context, value, index, list);
    });
    return results;
  };

  // **Reduce** builds up a single result from a list of values, aka `inject`,
  // or `foldl`. Delegates to **ECMAScript 5**'s native `reduce` if available.
  _.reduce = _.foldl = _.inject = function(obj, iterator, memo, context) {
    var initial = arguments.length > 2;
    if (obj == null) obj = [];
    if (nativeReduce && obj.reduce === nativeReduce) {
      if (context) iterator = _.bind(iterator, context);
      return initial ? obj.reduce(iterator, memo) : obj.reduce(iterator);
    }
    each(obj, function(value, index, list) {
      if (!initial) {
        memo = value;
        initial = true;
      } else {
        memo = iterator.call(context, memo, value, index, list);
      }
    });
    if (!initial) throw new TypeError('Reduce of empty array with no initial value');
    return memo;
  };

  // The right-associative version of reduce, also known as `foldr`.
  // Delegates to **ECMAScript 5**'s native `reduceRight` if available.
  _.reduceRight = _.foldr = function(obj, iterator, memo, context) {
    var initial = arguments.length > 2;
    if (obj == null) obj = [];
    if (nativeReduceRight && obj.reduceRight === nativeReduceRight) {
      if (context) iterator = _.bind(iterator, context);
      return arguments.length > 2 ? obj.reduceRight(iterator, memo) : obj.reduceRight(iterator);
    }
    var length = obj.length;
    if (length !== +length) {
      var keys = _.keys(obj);
      length = keys.length;
    }
    each(obj, function(value, index, list) {
      index = keys ? keys[--length] : --length;
      if (!initial) {
        memo = obj[index];
        initial = true;
      } else {
        memo = iterator.call(context, memo, obj[index], index, list);
      }
    });
    if (!initial) throw new TypeError('Reduce of empty array with no initial value');
    return memo;
  };

  // Return the first value which passes a truth test. Aliased as `detect`.
  _.find = _.detect = function(obj, iterator, context) {
    var result;
    any(obj, function(value, index, list) {
      if (iterator.call(context, value, index, list)) {
        result = value;
        return true;
      }
    });
    return result;
  };

  // Return all the elements that pass a truth test.
  // Delegates to **ECMAScript 5**'s native `filter` if available.
  // Aliased as `select`.
  _.filter = _.select = function(obj, iterator, context) {
    var results = [];
    if (obj == null) return results;
    if (nativeFilter && obj.filter === nativeFilter) return obj.filter(iterator, context);
    each(obj, function(value, index, list) {
      if (iterator.call(context, value, index, list)) results[results.length] = value;
    });
    return results;
  };

  // Return all the elements for which a truth test fails.
  _.reject = function(obj, iterator, context) {
    return _.filter(obj, function(value, index, list) {
      return !iterator.call(context, value, index, list);
    }, context);
  };

  // Determine whether all of the elements match a truth test.
  // Delegates to **ECMAScript 5**'s native `every` if available.
  // Aliased as `all`.
  _.every = _.all = function(obj, iterator, context) {
    iterator || (iterator = _.identity);
    var result = true;
    if (obj == null) return result;
    if (nativeEvery && obj.every === nativeEvery) return obj.every(iterator, context);
    each(obj, function(value, index, list) {
      if (!(result = result && iterator.call(context, value, index, list))) return breaker;
    });
    return !!result;
  };

  // Determine if at least one element in the object matches a truth test.
  // Delegates to **ECMAScript 5**'s native `some` if available.
  // Aliased as `any`.
  var any = _.some = _.any = function(obj, iterator, context) {
    iterator || (iterator = _.identity);
    var result = false;
    if (obj == null) return result;
    if (nativeSome && obj.some === nativeSome) return obj.some(iterator, context);
    each(obj, function(value, index, list) {
      if (result || (result = iterator.call(context, value, index, list))) return breaker;
    });
    return !!result;
  };

  // Determine if the array or object contains a given value (using `===`).
  // Aliased as `include`.
  _.contains = _.include = function(obj, target) {
    var found = false;
    if (obj == null) return found;
    if (nativeIndexOf && obj.indexOf === nativeIndexOf) return obj.indexOf(target) != -1;
    found = any(obj, function(value) {
      return value === target;
    });
    return found;
  };

  // Invoke a method (with arguments) on every item in a collection.
  _.invoke = function(obj, method) {
    var args = slice.call(arguments, 2);
    return _.map(obj, function(value) {
      return (_.isFunction(method) ? method : value[method]).apply(value, args);
    });
  };

  // Convenience version of a common use case of `map`: fetching a property.
  _.pluck = function(obj, key) {
    return _.map(obj, function(value){ return value[key]; });
  };

  // Convenience version of a common use case of `filter`: selecting only objects
  // with specific `key:value` pairs.
  _.where = function(obj, attrs) {
    if (_.isEmpty(attrs)) return [];
    return _.filter(obj, function(value) {
      for (var key in attrs) {
        if (attrs[key] !== value[key]) return false;
      }
      return true;
    });
  };

  // Return the maximum element or (element-based computation).
  // Can't optimize arrays of integers longer than 65,535 elements.
  // See: https://bugs.webkit.org/show_bug.cgi?id=80797
  _.max = function(obj, iterator, context) {
    if (!iterator && _.isArray(obj) && obj[0] === +obj[0] && obj.length < 65535) {
      return Math.max.apply(Math, obj);
    }
    if (!iterator && _.isEmpty(obj)) return -Infinity;
    var result = {computed : -Infinity};
    each(obj, function(value, index, list) {
      var computed = iterator ? iterator.call(context, value, index, list) : value;
      computed >= result.computed && (result = {value : value, computed : computed});
    });
    return result.value;
  };

  // Return the minimum element (or element-based computation).
  _.min = function(obj, iterator, context) {
    if (!iterator && _.isArray(obj) && obj[0] === +obj[0] && obj.length < 65535) {
      return Math.min.apply(Math, obj);
    }
    if (!iterator && _.isEmpty(obj)) return Infinity;
    var result = {computed : Infinity};
    each(obj, function(value, index, list) {
      var computed = iterator ? iterator.call(context, value, index, list) : value;
      computed < result.computed && (result = {value : value, computed : computed});
    });
    return result.value;
  };

  // Shuffle an array.
  _.shuffle = function(obj) {
    var rand;
    var index = 0;
    var shuffled = [];
    each(obj, function(value) {
      rand = _.random(index++);
      shuffled[index - 1] = shuffled[rand];
      shuffled[rand] = value;
    });
    return shuffled;
  };

  // An internal function to generate lookup iterators.
  var lookupIterator = function(value) {
    return _.isFunction(value) ? value : function(obj){ return obj[value]; };
  };

  // Sort the object's values by a criterion produced by an iterator.
  _.sortBy = function(obj, value, context) {
    var iterator = lookupIterator(value);
    return _.pluck(_.map(obj, function(value, index, list) {
      return {
        value : value,
        index : index,
        criteria : iterator.call(context, value, index, list)
      };
    }).sort(function(left, right) {
      var a = left.criteria;
      var b = right.criteria;
      if (a !== b) {
        if (a > b || a === void 0) return 1;
        if (a < b || b === void 0) return -1;
      }
      return left.index < right.index ? -1 : 1;
    }), 'value');
  };

  // An internal function used for aggregate "group by" operations.
  var group = function(obj, value, context, behavior) {
    var result = {};
    var iterator = lookupIterator(value);
    each(obj, function(value, index) {
      var key = iterator.call(context, value, index, obj);
      behavior(result, key, value);
    });
    return result;
  };

  // Groups the object's values by a criterion. Pass either a string attribute
  // to group by, or a function that returns the criterion.
  _.groupBy = function(obj, value, context) {
    return group(obj, value, context, function(result, key, value) {
      (_.has(result, key) ? result[key] : (result[key] = [])).push(value);
    });
  };

  // Counts instances of an object that group by a certain criterion. Pass
  // either a string attribute to count by, or a function that returns the
  // criterion.
  _.countBy = function(obj, value, context) {
    return group(obj, value, context, function(result, key, value) {
      if (!_.has(result, key)) result[key] = 0;
      result[key]++;
    });
  };

  // Use a comparator function to figure out the smallest index at which
  // an object should be inserted so as to maintain order. Uses binary search.
  _.sortedIndex = function(array, obj, iterator, context) {
    iterator = iterator == null ? _.identity : lookupIterator(iterator);
    var value = iterator.call(context, obj);
    var low = 0, high = array.length;
    while (low < high) {
      var mid = (low + high) >>> 1;
      iterator.call(context, array[mid]) < value ? low = mid + 1 : high = mid;
    }
    return low;
  };

  // Safely convert anything iterable into a real, live array.
  _.toArray = function(obj) {
    if (!obj) return [];
    if (obj.length === +obj.length) return slice.call(obj);
    return _.values(obj);
  };

  // Return the number of elements in an object.
  _.size = function(obj) {
    if (obj == null) return 0;
    return (obj.length === +obj.length) ? obj.length : _.keys(obj).length;
  };

  // Array Functions
  // ---------------

  // Get the first element of an array. Passing **n** will return the first N
  // values in the array. Aliased as `head` and `take`. The **guard** check
  // allows it to work with `_.map`.
  _.first = _.head = _.take = function(array, n, guard) {
    if (array == null) return void 0;
    return (n != null) && !guard ? slice.call(array, 0, n) : array[0];
  };

  // Returns everything but the last entry of the array. Especially useful on
  // the arguments object. Passing **n** will return all the values in
  // the array, excluding the last N. The **guard** check allows it to work with
  // `_.map`.
  _.initial = function(array, n, guard) {
    return slice.call(array, 0, array.length - ((n == null) || guard ? 1 : n));
  };

  // Get the last element of an array. Passing **n** will return the last N
  // values in the array. The **guard** check allows it to work with `_.map`.
  _.last = function(array, n, guard) {
    if (array == null) return void 0;
    if ((n != null) && !guard) {
      return slice.call(array, Math.max(array.length - n, 0));
    } else {
      return array[array.length - 1];
    }
  };

  // Returns everything but the first entry of the array. Aliased as `tail` and `drop`.
  // Especially useful on the arguments object. Passing an **n** will return
  // the rest N values in the array. The **guard**
  // check allows it to work with `_.map`.
  _.rest = _.tail = _.drop = function(array, n, guard) {
    return slice.call(array, (n == null) || guard ? 1 : n);
  };

  // Trim out all falsy values from an array.
  _.compact = function(array) {
    return _.filter(array, function(value){ return !!value; });
  };

  // Internal implementation of a recursive `flatten` function.
  var flatten = function(input, shallow, output) {
    each(input, function(value) {
      if (_.isArray(value)) {
        shallow ? push.apply(output, value) : flatten(value, shallow, output);
      } else {
        output.push(value);
      }
    });
    return output;
  };

  // Return a completely flattened version of an array.
  _.flatten = function(array, shallow) {
    return flatten(array, shallow, []);
  };

  // Return a version of the array that does not contain the specified value(s).
  _.without = function(array) {
    return _.difference(array, slice.call(arguments, 1));
  };

  // Produce a duplicate-free version of the array. If the array has already
  // been sorted, you have the option of using a faster algorithm.
  // Aliased as `unique`.
  _.uniq = _.unique = function(array, isSorted, iterator, context) {
    var initial = iterator ? _.map(array, iterator, context) : array;
    var results = [];
    var seen = [];
    each(initial, function(value, index) {
      if (isSorted ? (!index || seen[seen.length - 1] !== value) : !_.contains(seen, value)) {
        seen.push(value);
        results.push(array[index]);
      }
    });
    return results;
  };

  // Produce an array that contains the union: each distinct element from all of
  // the passed-in arrays.
  _.union = function() {
    return _.uniq(concat.apply(ArrayProto, arguments));
  };

  // Produce an array that contains every item shared between all the
  // passed-in arrays.
  _.intersection = function(array) {
    var rest = slice.call(arguments, 1);
    return _.filter(_.uniq(array), function(item) {
      return _.every(rest, function(other) {
        return _.indexOf(other, item) >= 0;
      });
    });
  };

  // Take the difference between one array and a number of other arrays.
  // Only the elements present in just the first array will remain.
  _.difference = function(array) {
    var rest = concat.apply(ArrayProto, slice.call(arguments, 1));
    return _.filter(array, function(value){ return !_.contains(rest, value); });
  };

  // Zip together multiple lists into a single array -- elements that share
  // an index go together.
  _.zip = function() {
    var args = slice.call(arguments);
    var length = _.max(_.pluck(args, 'length'));
    var results = new Array(length);
    for (var i = 0; i < length; i++) {
      results[i] = _.pluck(args, "" + i);
    }
    return results;
  };

  // Converts lists into objects. Pass either a single array of `[key, value]`
  // pairs, or two parallel arrays of the same length -- one of keys, and one of
  // the corresponding values.
  _.object = function(list, values) {
    if (list == null) return {};
    var result = {};
    for (var i = 0, l = list.length; i < l; i++) {
      if (values) {
        result[list[i]] = values[i];
      } else {
        result[list[i][0]] = list[i][1];
      }
    }
    return result;
  };

  // If the browser doesn't supply us with indexOf (I'm looking at you, **MSIE**),
  // we need this function. Return the position of the first occurrence of an
  // item in an array, or -1 if the item is not included in the array.
  // Delegates to **ECMAScript 5**'s native `indexOf` if available.
  // If the array is large and already in sort order, pass `true`
  // for **isSorted** to use binary search.
  _.indexOf = function(array, item, isSorted) {
    if (array == null) return -1;
    var i = 0, l = array.length;
    if (isSorted) {
      if (typeof isSorted == 'number') {
        i = (isSorted < 0 ? Math.max(0, l + isSorted) : isSorted);
      } else {
        i = _.sortedIndex(array, item);
        return array[i] === item ? i : -1;
      }
    }
    if (nativeIndexOf && array.indexOf === nativeIndexOf) return array.indexOf(item, isSorted);
    for (; i < l; i++) if (array[i] === item) return i;
    return -1;
  };

  // Delegates to **ECMAScript 5**'s native `lastIndexOf` if available.
  _.lastIndexOf = function(array, item, from) {
    if (array == null) return -1;
    var hasIndex = from != null;
    if (nativeLastIndexOf && array.lastIndexOf === nativeLastIndexOf) {
      return hasIndex ? array.lastIndexOf(item, from) : array.lastIndexOf(item);
    }
    var i = (hasIndex ? from : array.length);
    while (i--) if (array[i] === item) return i;
    return -1;
  };

  // Generate an integer Array containing an arithmetic progression. A port of
  // the native Python `range()` function. See
  // [the Python documentation](http://docs.python.org/library/functions.html#range).
  _.range = function(start, stop, step) {
    if (arguments.length <= 1) {
      stop = start || 0;
      start = 0;
    }
    step = arguments[2] || 1;

    var len = Math.max(Math.ceil((stop - start) / step), 0);
    var idx = 0;
    var range = new Array(len);

    while(idx < len) {
      range[idx++] = start;
      start += step;
    }

    return range;
  };

  // Function (ahem) Functions
  // ------------------

  // Reusable constructor function for prototype setting.
  var ctor = function(){};

  // Create a function bound to a given object (assigning `this`, and arguments,
  // optionally). Binding with arguments is also known as `curry`.
  // Delegates to **ECMAScript 5**'s native `Function.bind` if available.
  // We check for `func.bind` first, to fail fast when `func` is undefined.
  _.bind = function bind(func, context) {
    var bound, args;
    if (func.bind === nativeBind && nativeBind) return nativeBind.apply(func, slice.call(arguments, 1));
    if (!_.isFunction(func)) throw new TypeError;
    args = slice.call(arguments, 2);
    return bound = function() {
      if (!(this instanceof bound)) return func.apply(context, args.concat(slice.call(arguments)));
      ctor.prototype = func.prototype;
      var self = new ctor;
      var result = func.apply(self, args.concat(slice.call(arguments)));
      if (Object(result) === result) return result;
      return self;
    };
  };

  // Bind all of an object's methods to that object. Useful for ensuring that
  // all callbacks defined on an object belong to it.
  _.bindAll = function(obj) {
    var funcs = slice.call(arguments, 1);
    if (funcs.length == 0) funcs = _.functions(obj);
    each(funcs, function(f) { obj[f] = _.bind(obj[f], obj); });
    return obj;
  };

  // Memoize an expensive function by storing its results.
  _.memoize = function(func, hasher) {
    var memo = {};
    hasher || (hasher = _.identity);
    return function() {
      var key = hasher.apply(this, arguments);
      return _.has(memo, key) ? memo[key] : (memo[key] = func.apply(this, arguments));
    };
  };

  // Delays a function for the given number of milliseconds, and then calls
  // it with the arguments supplied.
  _.delay = function(func, wait) {
    var args = slice.call(arguments, 2);
    return setTimeout(function(){ return func.apply(null, args); }, wait);
  };

  // Defers a function, scheduling it to run after the current call stack has
  // cleared.
  _.defer = function(func) {
    return _.delay.apply(_, [func, 1].concat(slice.call(arguments, 1)));
  };

  // Returns a function, that, when invoked, will only be triggered at most once
  // during a given window of time.
  _.throttle = function(func, wait) {
    var context, args, timeout, result;
    var previous = 0;
    var later = function() {
      previous = new Date;
      timeout = null;
      result = func.apply(context, args);
    };
    return function() {
      var now = new Date;
      var remaining = wait - (now - previous);
      context = this;
      args = arguments;
      if (remaining <= 0) {
        clearTimeout(timeout);
        previous = now;
        result = func.apply(context, args);
      } else if (!timeout) {
        timeout = setTimeout(later, remaining);
      }
      return result;
    };
  };

  // Returns a function, that, as long as it continues to be invoked, will not
  // be triggered. The function will be called after it stops being called for
  // N milliseconds. If `immediate` is passed, trigger the function on the
  // leading edge, instead of the trailing.
  _.debounce = function(func, wait, immediate) {
    var timeout, result;
    return function() {
      var context = this, args = arguments;
      var later = function() {
        timeout = null;
        if (!immediate) result = func.apply(context, args);
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) result = func.apply(context, args);
      return result;
    };
  };

  // Returns a function that will be executed at most one time, no matter how
  // often you call it. Useful for lazy initialization.
  _.once = function(func) {
    var ran = false, memo;
    return function() {
      if (ran) return memo;
      ran = true;
      memo = func.apply(this, arguments);
      func = null;
      return memo;
    };
  };

  // Returns the first function passed as an argument to the second,
  // allowing you to adjust arguments, run code before and after, and
  // conditionally execute the original function.
  _.wrap = function(func, wrapper) {
    return function() {
      var args = [func];
      push.apply(args, arguments);
      return wrapper.apply(this, args);
    };
  };

  // Returns a function that is the composition of a list of functions, each
  // consuming the return value of the function that follows.
  _.compose = function() {
    var funcs = arguments;
    return function() {
      var args = arguments;
      for (var i = funcs.length - 1; i >= 0; i--) {
        args = [funcs[i].apply(this, args)];
      }
      return args[0];
    };
  };

  // Returns a function that will only be executed after being called N times.
  _.after = function(times, func) {
    if (times <= 0) return func();
    return function() {
      if (--times < 1) {
        return func.apply(this, arguments);
      }
    };
  };

  // Object Functions
  // ----------------

  // Retrieve the names of an object's properties.
  // Delegates to **ECMAScript 5**'s native `Object.keys`
  _.keys = nativeKeys || function(obj) {
    if (obj !== Object(obj)) throw new TypeError('Invalid object');
    var keys = [];
    for (var key in obj) if (_.has(obj, key)) keys[keys.length] = key;
    return keys;
  };

  // Retrieve the values of an object's properties.
  _.values = function(obj) {
    var values = [];
    for (var key in obj) if (_.has(obj, key)) values.push(obj[key]);
    return values;
  };

  // Convert an object into a list of `[key, value]` pairs.
  _.pairs = function(obj) {
    var pairs = [];
    for (var key in obj) if (_.has(obj, key)) pairs.push([key, obj[key]]);
    return pairs;
  };

  // Invert the keys and values of an object. The values must be serializable.
  _.invert = function(obj) {
    var result = {};
    for (var key in obj) if (_.has(obj, key)) result[obj[key]] = key;
    return result;
  };

  // Return a sorted list of the function names available on the object.
  // Aliased as `methods`
  _.functions = _.methods = function(obj) {
    var names = [];
    for (var key in obj) {
      if (_.isFunction(obj[key])) names.push(key);
    }
    return names.sort();
  };

  // Extend a given object with all the properties in passed-in object(s).
  _.extend = function(obj) {
    each(slice.call(arguments, 1), function(source) {
      for (var prop in source) {
        obj[prop] = source[prop];
      }
    });
    return obj;
  };

  // Return a copy of the object only containing the whitelisted properties.
  _.pick = function(obj) {
    var copy = {};
    var keys = concat.apply(ArrayProto, slice.call(arguments, 1));
    each(keys, function(key) {
      if (key in obj) copy[key] = obj[key];
    });
    return copy;
  };

   // Return a copy of the object without the blacklisted properties.
  _.omit = function(obj) {
    var copy = {};
    var keys = concat.apply(ArrayProto, slice.call(arguments, 1));
    for (var key in obj) {
      if (!_.contains(keys, key)) copy[key] = obj[key];
    }
    return copy;
  };

  // Fill in a given object with default properties.
  _.defaults = function(obj) {
    each(slice.call(arguments, 1), function(source) {
      for (var prop in source) {
        if (obj[prop] == null) obj[prop] = source[prop];
      }
    });
    return obj;
  };

  // Create a (shallow-cloned) duplicate of an object.
  _.clone = function(obj) {
    if (!_.isObject(obj)) return obj;
    return _.isArray(obj) ? obj.slice() : _.extend({}, obj);
  };

  // Invokes interceptor with the obj, and then returns obj.
  // The primary purpose of this method is to "tap into" a method chain, in
  // order to perform operations on intermediate results within the chain.
  _.tap = function(obj, interceptor) {
    interceptor(obj);
    return obj;
  };

  // Internal recursive comparison function for `isEqual`.
  var eq = function(a, b, aStack, bStack) {
    // Identical objects are equal. `0 === -0`, but they aren't identical.
    // See the Harmony `egal` proposal: http://wiki.ecmascript.org/doku.php?id=harmony:egal.
    if (a === b) return a !== 0 || 1 / a == 1 / b;
    // A strict comparison is necessary because `null == undefined`.
    if (a == null || b == null) return a === b;
    // Unwrap any wrapped objects.
    if (a instanceof _) a = a._wrapped;
    if (b instanceof _) b = b._wrapped;
    // Compare `[[Class]]` names.
    var className = toString.call(a);
    if (className != toString.call(b)) return false;
    switch (className) {
      // Strings, numbers, dates, and booleans are compared by value.
      case '[object String]':
        // Primitives and their corresponding object wrappers are equivalent; thus, `"5"` is
        // equivalent to `new String("5")`.
        return a == String(b);
      case '[object Number]':
        // `NaN`s are equivalent, but non-reflexive. An `egal` comparison is performed for
        // other numeric values.
        return a != +a ? b != +b : (a == 0 ? 1 / a == 1 / b : a == +b);
      case '[object Date]':
      case '[object Boolean]':
        // Coerce dates and booleans to numeric primitive values. Dates are compared by their
        // millisecond representations. Note that invalid dates with millisecond representations
        // of `NaN` are not equivalent.
        return +a == +b;
      // RegExps are compared by their source patterns and flags.
      case '[object RegExp]':
        return a.source == b.source &&
               a.global == b.global &&
               a.multiline == b.multiline &&
               a.ignoreCase == b.ignoreCase;
    }
    if (typeof a != 'object' || typeof b != 'object') return false;
    // Assume equality for cyclic structures. The algorithm for detecting cyclic
    // structures is adapted from ES 5.1 section 15.12.3, abstract operation `JO`.
    var length = aStack.length;
    while (length--) {
      // Linear search. Performance is inversely proportional to the number of
      // unique nested structures.
      if (aStack[length] == a) return bStack[length] == b;
    }
    // Add the first object to the stack of traversed objects.
    aStack.push(a);
    bStack.push(b);
    var size = 0, result = true;
    // Recursively compare objects and arrays.
    if (className == '[object Array]') {
      // Compare array lengths to determine if a deep comparison is necessary.
      size = a.length;
      result = size == b.length;
      if (result) {
        // Deep compare the contents, ignoring non-numeric properties.
        while (size--) {
          if (!(result = eq(a[size], b[size], aStack, bStack))) break;
        }
      }
    } else {
      // Objects with different constructors are not equivalent, but `Object`s
      // from different frames are.
      var aCtor = a.constructor, bCtor = b.constructor;
      if (aCtor !== bCtor && !(_.isFunction(aCtor) && (aCtor instanceof aCtor) &&
                               _.isFunction(bCtor) && (bCtor instanceof bCtor))) {
        return false;
      }
      // Deep compare objects.
      for (var key in a) {
        if (_.has(a, key)) {
          // Count the expected number of properties.
          size++;
          // Deep compare each member.
          if (!(result = _.has(b, key) && eq(a[key], b[key], aStack, bStack))) break;
        }
      }
      // Ensure that both objects contain the same number of properties.
      if (result) {
        for (key in b) {
          if (_.has(b, key) && !(size--)) break;
        }
        result = !size;
      }
    }
    // Remove the first object from the stack of traversed objects.
    aStack.pop();
    bStack.pop();
    return result;
  };

  // Perform a deep comparison to check if two objects are equal.
  _.isEqual = function(a, b) {
    return eq(a, b, [], []);
  };

  // Is a given array, string, or object empty?
  // An "empty" object has no enumerable own-properties.
  _.isEmpty = function(obj) {
    if (obj == null) return true;
    if (_.isArray(obj) || _.isString(obj)) return obj.length === 0;
    for (var key in obj) if (_.has(obj, key)) return false;
    return true;
  };

  // Is a given value a DOM element?
  _.isElement = function(obj) {
    return !!(obj && obj.nodeType === 1);
  };

  // Is a given value an array?
  // Delegates to ECMA5's native Array.isArray
  _.isArray = nativeIsArray || function(obj) {
    return toString.call(obj) == '[object Array]';
  };

  // Is a given variable an object?
  _.isObject = function(obj) {
    return obj === Object(obj);
  };

  // Add some isType methods: isArguments, isFunction, isString, isNumber, isDate, isRegExp.
  each(['Arguments', 'Function', 'String', 'Number', 'Date', 'RegExp'], function(name) {
    _['is' + name] = function(obj) {
      return toString.call(obj) == '[object ' + name + ']';
    };
  });

  // Define a fallback version of the method in browsers (ahem, IE), where
  // there isn't any inspectable "Arguments" type.
  if (!_.isArguments(arguments)) {
    _.isArguments = function(obj) {
      return !!(obj && _.has(obj, 'callee'));
    };
  }

  // Optimize `isFunction` if appropriate.
  if (typeof (/./) !== 'function') {
    _.isFunction = function(obj) {
      return typeof obj === 'function';
    };
  }

  // Is a given object a finite number?
  _.isFinite = function(obj) {
    return isFinite( obj ) && !isNaN( parseFloat(obj) );
  };

  // Is the given value `NaN`? (NaN is the only number which does not equal itself).
  _.isNaN = function(obj) {
    return _.isNumber(obj) && obj != +obj;
  };

  // Is a given value a boolean?
  _.isBoolean = function(obj) {
    return obj === true || obj === false || toString.call(obj) == '[object Boolean]';
  };

  // Is a given value equal to null?
  _.isNull = function(obj) {
    return obj === null;
  };

  // Is a given variable undefined?
  _.isUndefined = function(obj) {
    return obj === void 0;
  };

  // Shortcut function for checking if an object has a given property directly
  // on itself (in other words, not on a prototype).
  _.has = function(obj, key) {
    return hasOwnProperty.call(obj, key);
  };

  // Utility Functions
  // -----------------

  // Run Underscore.js in *noConflict* mode, returning the `_` variable to its
  // previous owner. Returns a reference to the Underscore object.
  _.noConflict = function() {
    root._ = previousUnderscore;
    return this;
  };

  // Keep the identity function around for default iterators.
  _.identity = function(value) {
    return value;
  };

  // Run a function **n** times.
  _.times = function(n, iterator, context) {
    for (var i = 0; i < n; i++) iterator.call(context, i);
  };

  // Return a random integer between min and max (inclusive).
  _.random = function(min, max) {
    if (max == null) {
      max = min;
      min = 0;
    }
    return min + (0 | Math.random() * (max - min + 1));
  };

  // List of HTML entities for escaping.
  var entityMap = {
    escape: {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#x27;',
      '/': '&#x2F;'
    }
  };
  entityMap.unescape = _.invert(entityMap.escape);

  // Regexes containing the keys and values listed immediately above.
  var entityRegexes = {
    escape:   new RegExp('[' + _.keys(entityMap.escape).join('') + ']', 'g'),
    unescape: new RegExp('(' + _.keys(entityMap.unescape).join('|') + ')', 'g')
  };

  // Functions for escaping and unescaping strings to/from HTML interpolation.
  _.each(['escape', 'unescape'], function(method) {
    _[method] = function(string) {
      if (string == null) return '';
      return ('' + string).replace(entityRegexes[method], function(match) {
        return entityMap[method][match];
      });
    };
  });

  // If the value of the named property is a function then invoke it;
  // otherwise, return it.
  _.result = function(object, property) {
    if (object == null) return null;
    var value = object[property];
    return _.isFunction(value) ? value.call(object) : value;
  };

  // Add your own custom functions to the Underscore object.
  _.mixin = function(obj) {
    each(_.functions(obj), function(name){
      var func = _[name] = obj[name];
      _.prototype[name] = function() {
        var args = [this._wrapped];
        push.apply(args, arguments);
        return result.call(this, func.apply(_, args));
      };
    });
  };

  // Generate a unique integer id (unique within the entire client session).
  // Useful for temporary DOM ids.
  var idCounter = 0;
  _.uniqueId = function(prefix) {
    var id = idCounter++;
    return prefix ? prefix + id : id;
  };

  // By default, Underscore uses ERB-style template delimiters, change the
  // following template settings to use alternative delimiters.
  _.templateSettings = {
    evaluate    : /<%([\s\S]+?)%>/g,
    interpolate : /<%=([\s\S]+?)%>/g,
    escape      : /<%-([\s\S]+?)%>/g
  };

  // When customizing `templateSettings`, if you don't want to define an
  // interpolation, evaluation or escaping regex, we need one that is
  // guaranteed not to match.
  var noMatch = /(.)^/;

  // Certain characters need to be escaped so that they can be put into a
  // string literal.
  var escapes = {
    "'":      "'",
    '\\':     '\\',
    '\r':     'r',
    '\n':     'n',
    '\t':     't',
    '\u2028': 'u2028',
    '\u2029': 'u2029'
  };

  var escaper = /\\|'|\r|\n|\t|\u2028|\u2029/g;

  // JavaScript micro-templating, similar to John Resig's implementation.
  // Underscore templating handles arbitrary delimiters, preserves whitespace,
  // and correctly escapes quotes within interpolated code.
  _.template = function(text, data, settings) {
    settings = _.defaults({}, settings, _.templateSettings);

    // Combine delimiters into one regular expression via alternation.
    var matcher = new RegExp([
      (settings.escape || noMatch).source,
      (settings.interpolate || noMatch).source,
      (settings.evaluate || noMatch).source
    ].join('|') + '|$', 'g');

    // Compile the template source, escaping string literals appropriately.
    var index = 0;
    var source = "__p+='";
    text.replace(matcher, function(match, escape, interpolate, evaluate, offset) {
      source += text.slice(index, offset)
        .replace(escaper, function(match) { return '\\' + escapes[match]; });
      source +=
        escape ? "'+\n((__t=(" + escape + "))==null?'':_.escape(__t))+\n'" :
        interpolate ? "'+\n((__t=(" + interpolate + "))==null?'':__t)+\n'" :
        evaluate ? "';\n" + evaluate + "\n__p+='" : '';
      index = offset + match.length;
    });
    source += "';\n";

    // If a variable is not specified, place data values in local scope.
    if (!settings.variable) source = 'with(obj||{}){\n' + source + '}\n';

    source = "var __t,__p='',__j=Array.prototype.join," +
      "print=function(){__p+=__j.call(arguments,'');};\n" +
      source + "return __p;\n";

    try {
      var render = new Function(settings.variable || 'obj', '_', source);
    } catch (e) {
      e.source = source;
      throw e;
    }

    if (data) return render(data, _);
    var template = function(data) {
      return render.call(this, data, _);
    };

    // Provide the compiled function source as a convenience for precompilation.
    template.source = 'function(' + (settings.variable || 'obj') + '){\n' + source + '}';

    return template;
  };

  // Add a "chain" function, which will delegate to the wrapper.
  _.chain = function(obj) {
    return _(obj).chain();
  };

  // OOP
  // ---------------
  // If Underscore is called as a function, it returns a wrapped object that
  // can be used OO-style. This wrapper holds altered versions of all the
  // underscore functions. Wrapped objects may be chained.

  // Helper function to continue chaining intermediate results.
  var result = function(obj) {
    return this._chain ? _(obj).chain() : obj;
  };

  // Add all of the Underscore functions to the wrapper object.
  _.mixin(_);

  // Add all mutator Array functions to the wrapper.
  each(['pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      var obj = this._wrapped;
      method.apply(obj, arguments);
      if ((name == 'shift' || name == 'splice') && obj.length === 0) delete obj[0];
      return result.call(this, obj);
    };
  });

  // Add all accessor Array functions to the wrapper.
  each(['concat', 'join', 'slice'], function(name) {
    var method = ArrayProto[name];
    _.prototype[name] = function() {
      return result.call(this, method.apply(this._wrapped, arguments));
    };
  });

  _.extend(_.prototype, {

    // Start chaining a wrapped Underscore object.
    chain: function() {
      this._chain = true;
      return this;
    },

    // Extracts the result from a wrapped and chained object.
    value: function() {
      return this._wrapped;
    }

  });

}).call(this);
if (!(typeof window.google === 'object' && window.google.maps)) {
  throw 'Google Maps API is required. Please register the following JavaScript library http://maps.google.com/maps/api/js?sensor=true.'
}

var extend_object = function(obj, new_obj) {
  var name;

  if (obj === new_obj) {
    return obj;
  }

  for (name in new_obj) {
    obj[name] = new_obj[name];
  }

  return obj;
};

var replace_object = function(obj, replace) {
  var name;

  if (obj === replace) {
    return obj;
  }

  for (name in replace) {
    if (obj[name] != undefined) {
      obj[name] = replace[name];
    }
  }

  return obj;
};

var array_map = function(array, callback) {
  var original_callback_params = Array.prototype.slice.call(arguments, 2),
      array_return = [],
      array_length = array.length,
      i;

  if (Array.prototype.map && array.map === Array.prototype.map) {
    array_return = Array.prototype.map.call(array, function(item) {
      callback_params = original_callback_params;
      callback_params.splice(0, 0, item);

      return callback.apply(this, callback_params);
    });
  }
  else {
    for (i = 0; i < array_length; i++) {
      callback_params = original_callback_params;
      callback_params.splice(0, 0, array[i]);
      array_return.push(callback.apply(this, callback_params));
    }
  }

  return array_return;
};

var array_flat = function(array) {
  var new_array = [],
      i;

  for (i = 0; i < array.length; i++) {
    new_array = new_array.concat(array[i]);
  }

  return new_array;
};

var coordsToLatLngs = function(coords, useGeoJSON) {
  var first_coord = coords[0],
      second_coord = coords[1];

  if (useGeoJSON) {
    first_coord = coords[1];
    second_coord = coords[0];
  }

  return new google.maps.LatLng(first_coord, second_coord);
};

var arrayToLatLng = function(coords, useGeoJSON) {
  var i;

  for (i = 0; i < coords.length; i++) {
    if (!(coords[i] instanceof google.maps.LatLng)) {
      if (coords[i].length > 0 && typeof(coords[i][0]) === "object") {
        coords[i] = arrayToLatLng(coords[i], useGeoJSON);
      }
      else {
        coords[i] = coordsToLatLngs(coords[i], useGeoJSON);
      }
    }
  }

  return coords;
};

var getElementById = function(id, context) {
  var element,
  id = id.replace('#', '');

  if ('jQuery' in this && context) {
    element = $('#' + id, context)[0];
  } else {
    element = document.getElementById(id);
  };

  return element;
};

var findAbsolutePosition = function(obj)  {
  var curleft = 0,
      curtop = 0;

  if (obj.offsetParent) {
    do {
      curleft += obj.offsetLeft;
      curtop += obj.offsetTop;
    } while (obj = obj.offsetParent);
  }

  return [curleft, curtop];
};

var GMaps = (function(global) {
  "use strict";

  var doc = document;

  var GMaps = function(options) {
    if (!this) return new GMaps(options);

    options.zoom = options.zoom || 15;
    options.mapType = options.mapType || 'roadmap';

    var self = this,
        i,
        events_that_hide_context_menu = [
          'bounds_changed', 'center_changed', 'click', 'dblclick', 'drag',
          'dragend', 'dragstart', 'idle', 'maptypeid_changed', 'projection_changed',
          'resize', 'tilesloaded', 'zoom_changed'
        ],
        events_that_doesnt_hide_context_menu = ['mousemove', 'mouseout', 'mouseover'],
        options_to_be_deleted = ['el', 'lat', 'lng', 'mapType', 'width', 'height', 'markerClusterer', 'enableNewStyle'],
        container_id = options.el || options.div,
        markerClustererFunction = options.markerClusterer,
        mapType = google.maps.MapTypeId[options.mapType.toUpperCase()],
        map_center = new google.maps.LatLng(options.lat, options.lng),
        zoomControl = options.zoomControl || true,
        zoomControlOpt = options.zoomControlOpt || {
          style: 'DEFAULT',
          position: 'TOP_LEFT'
        },
        zoomControlStyle = zoomControlOpt.style || 'DEFAULT',
        zoomControlPosition = zoomControlOpt.position || 'TOP_LEFT',
        panControl = options.panControl || true,
        mapTypeControl = options.mapTypeControl || true,
        scaleControl = options.scaleControl || true,
        streetViewControl = options.streetViewControl || true,
        overviewMapControl = overviewMapControl || true,
        map_options = {},
        map_base_options = {
          zoom: this.zoom,
          center: map_center,
          mapTypeId: mapType
        },
        map_controls_options = {
          panControl: panControl,
          zoomControl: zoomControl,
          zoomControlOptions: {
            style: google.maps.ZoomControlStyle[zoomControlStyle],
            position: google.maps.ControlPosition[zoomControlPosition]
          },
          mapTypeControl: mapTypeControl,
          scaleControl: scaleControl,
          streetViewControl: streetViewControl,
          overviewMapControl: overviewMapControl
        };

    if (typeof(options.el) === 'string' || typeof(options.div) === 'string') {
      this.el = getElementById(container_id, options.context);
    } else {
      this.el = container_id;
    }

    if (typeof(this.el) === 'undefined' || this.el === null) {
      throw 'No element defined.';
    }

    window.context_menu = window.context_menu || {};
    window.context_menu[self.el.id] = {};

    this.controls = [];
    this.overlays = [];
    this.layers = []; // array with kml/georss and fusiontables layers, can be as many
    this.singleLayers = {}; // object with the other layers, only one per layer
    this.markers = [];
    this.polylines = [];
    this.routes = [];
    this.polygons = [];
    this.infoWindow = null;
    this.overlay_el = null;
    this.zoom = options.zoom;
    this.registered_events = {};

    this.el.style.width = options.width || this.el.scrollWidth || this.el.offsetWidth;
    this.el.style.height = options.height || this.el.scrollHeight || this.el.offsetHeight;

    google.maps.visualRefresh = options.enableNewStyle;

    for (i = 0; i < options_to_be_deleted.length; i++) {
      delete options[options_to_be_deleted[i]];
    }

    if(options.disableDefaultUI != true) {
      map_base_options = extend_object(map_base_options, map_controls_options);
    }

    map_options = extend_object(map_base_options, options);

    for (i = 0; i < events_that_hide_context_menu.length; i++) {
      delete map_options[events_that_hide_context_menu[i]];
    }

    for (i = 0; i < events_that_doesnt_hide_context_menu.length; i++) {
      delete map_options[events_that_doesnt_hide_context_menu[i]];
    }

    this.map = new google.maps.Map(this.el, map_options);

    if (markerClustererFunction) {
      this.markerClusterer = markerClustererFunction.apply(this, [this.map]);
    }

    var buildContextMenuHTML = function(control, e) {
      var html = '',
          options = window.context_menu[self.el.id][control];

      for (var i in options){
        if (options.hasOwnProperty(i)) {
          var option = options[i];

          html += '<li><a id="' + control + '_' + i + '" href="#">' + option.title + '</a></li>';
        }
      }

      if (!getElementById('gmaps_context_menu')) return;

      var context_menu_element = getElementById('gmaps_context_menu');
      
      context_menu_element.innerHTML = html;

      var context_menu_items = context_menu_element.getElementsByTagName('a'),
          context_menu_items_count = context_menu_items.length,
          i;

      for (i = 0; i < context_menu_items_count; i++) {
        var context_menu_item = context_menu_items[i];

        var assign_menu_item_action = function(ev){
          ev.preventDefault();

          options[this.id.replace(control + '_', '')].action.apply(self, [e]);
          self.hideContextMenu();
        };

        google.maps.event.clearListeners(context_menu_item, 'click');
        google.maps.event.addDomListenerOnce(context_menu_item, 'click', assign_menu_item_action, false);
      }

      var position = findAbsolutePosition.apply(this, [self.el]),
          left = position[0] + e.pixel.x - 15,
          top = position[1] + e.pixel.y- 15;

      context_menu_element.style.left = left + "px";
      context_menu_element.style.top = top + "px";

      context_menu_element.style.display = 'block';
    };

    this.buildContextMenu = function(control, e) {
      if (control === 'marker') {
        e.pixel = {};

        var overlay = new google.maps.OverlayView();
        overlay.setMap(self.map);
        
        overlay.draw = function() {
          var projection = overlay.getProjection(),
              position = e.marker.getPosition();
          
          e.pixel = projection.fromLatLngToContainerPixel(position);

          buildContextMenuHTML(control, e);
        };
      }
      else {
        buildContextMenuHTML(control, e);
      }
    };

    this.setContextMenu = function(options) {
      window.context_menu[self.el.id][options.control] = {};

      var i,
          ul = doc.createElement('ul');

      for (i in options.options) {
        if (options.options.hasOwnProperty(i)) {
          var option = options.options[i];

          window.context_menu[self.el.id][options.control][option.name] = {
            title: option.title,
            action: option.action
          };
        }
      }

      ul.id = 'gmaps_context_menu';
      ul.style.display = 'none';
      ul.style.position = 'absolute';
      ul.style.minWidth = '100px';
      ul.style.background = 'white';
      ul.style.listStyle = 'none';
      ul.style.padding = '8px';
      ul.style.boxShadow = '2px 2px 6px #ccc';

      doc.body.appendChild(ul);

      var context_menu_element = getElementById('gmaps_context_menu')

      google.maps.event.addDomListener(context_menu_element, 'mouseout', function(ev) {
        if (!ev.relatedTarget || !this.contains(ev.relatedTarget)) {
          window.setTimeout(function(){
            context_menu_element.style.display = 'none';
          }, 400);
        }
      }, false);
    };

    this.hideContextMenu = function() {
      var context_menu_element = getElementById('gmaps_context_menu');

      if (context_menu_element) {
        context_menu_element.style.display = 'none';
      }
    };

    var setupListener = function(object, name) {
      google.maps.event.addListener(object, name, function(e){
        if (e == undefined) {
          e = this;
        }

        options[name].apply(this, [e]);

        self.hideContextMenu();
      });
    };

    //google.maps.event.addListener(this.map, 'idle', this.hideContextMenu);
    google.maps.event.addListener(this.map, 'zoom_changed', this.hideContextMenu);

    for (var ev = 0; ev < events_that_hide_context_menu.length; ev++) {
      var name = events_that_hide_context_menu[ev];

      if (name in options) {
        setupListener(this.map, name);
      }
    }

    for (var ev = 0; ev < events_that_doesnt_hide_context_menu.length; ev++) {
      var name = events_that_doesnt_hide_context_menu[ev];

      if (name in options) {
        setupListener(this.map, name);
      }
    }

    google.maps.event.addListener(this.map, 'rightclick', function(e) {
      if (options.rightclick) {
        options.rightclick.apply(this, [e]);
      }

      if(window.context_menu[self.el.id]['map'] != undefined) {
        self.buildContextMenu('map', e);
      }
    });

    this.refresh = function() {
      google.maps.event.trigger(this.map, 'resize');
    };

    this.fitZoom = function() {
      var latLngs = [],
          markers_length = this.markers.length,
          i;

      for (i = 0; i < markers_length; i++) {
        if(typeof(this.markers[i].visible) === 'boolean' && this.markers[i].visible) {
          latLngs.push(this.markers[i].getPosition());
        }
      }

      this.fitLatLngBounds(latLngs);
    };

    this.fitLatLngBounds = function(latLngs) {
      var total = latLngs.length,
          bounds = new google.maps.LatLngBounds(),
          i;

      for(i = 0; i < total; i++) {
        bounds.extend(latLngs[i]);
      }

      this.map.fitBounds(bounds);
    };

    this.setCenter = function(lat, lng, callback) {
      this.map.panTo(new google.maps.LatLng(lat, lng));

      if (callback) {
        callback();
      }
    };

    this.getElement = function() {
      return this.el;
    };

    this.zoomIn = function(value) {
      value = value || 1;

      this.zoom = this.map.getZoom() + value;
      this.map.setZoom(this.zoom);
    };

    this.zoomOut = function(value) {
      value = value || 1;

      this.zoom = this.map.getZoom() - value;
      this.map.setZoom(this.zoom);
    };

    var native_methods = [],
        method;

    for (method in this.map) {
      if (typeof(this.map[method]) == 'function' && !this[method]) {
        native_methods.push(method);
      }
    }

    for (i = 0; i < native_methods.length; i++) {
      (function(gmaps, scope, method_name) {
        gmaps[method_name] = function(){
          return scope[method_name].apply(scope, arguments);
        };
      })(this, this.map, native_methods[i]);
    }
  };

  return GMaps;
})(this);

GMaps.prototype.createControl = function(options) {
  var control = document.createElement('div');

  control.style.cursor = 'pointer';
  
  if (options.disableDefaultStyles !== true) {
    control.style.fontFamily = 'Roboto, Arial, sans-serif';
    control.style.fontSize = '11px';
    control.style.boxShadow = 'rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px';
  }

  for (var option in options.style) {
    control.style[option] = options.style[option];
  }

  if (options.id) {
    control.id = options.id;
  }

  if (options.classes) {
    control.className = options.classes;
  }

  if (options.content) {
    if (typeof options.content === 'string') {
      control.innerHTML = options.content;
    }
    else if (options.content instanceof HTMLElement) {
      control.appendChild(options.content);
    }
  }

  if (options.position) {
    control.position = google.maps.ControlPosition[options.position.toUpperCase()];
  }

  for (var ev in options.events) {
    (function(object, name) {
      google.maps.event.addDomListener(object, name, function(){
        options.events[name].apply(this, [this]);
      });
    })(control, ev);
  }

  control.index = 1;

  return control;
};

GMaps.prototype.addControl = function(options) {
  var control = this.createControl(options);
  
  this.controls.push(control);
  this.map.controls[control.position].push(control);

  return control;
};

GMaps.prototype.removeControl = function(control) {
  var position = null,
      i;

  for (i = 0; i < this.controls.length; i++) {
    if (this.controls[i] == control) {
      position = this.controls[i].position;
      this.controls.splice(i, 1);
    }
  }

  if (position) {
    for (i = 0; i < this.map.controls.length; i++) {
      var controlsForPosition = this.map.controls[control.position];

      if (controlsForPosition.getAt(i) == control) {
        controlsForPosition.removeAt(i);

        break;
      }
    }
  }

  return control;
};

GMaps.prototype.createMarker = function(options) {
  if (options.lat == undefined && options.lng == undefined && options.position == undefined) {
    throw 'No latitude or longitude defined.';
  }

  var self = this,
      details = options.details,
      fences = options.fences,
      outside = options.outside,
      base_options = {
        position: new google.maps.LatLng(options.lat, options.lng),
        map: null
      },
      marker_options = extend_object(base_options, options);

  delete marker_options.lat;
  delete marker_options.lng;
  delete marker_options.fences;
  delete marker_options.outside;

  var marker = new google.maps.Marker(marker_options);

  marker.fences = fences;

  if (options.infoWindow) {
    marker.infoWindow = new google.maps.InfoWindow(options.infoWindow);

    var info_window_events = ['closeclick', 'content_changed', 'domready', 'position_changed', 'zindex_changed'];

    for (var ev = 0; ev < info_window_events.length; ev++) {
      (function(object, name) {
        if (options.infoWindow[name]) {
          google.maps.event.addListener(object, name, function(e){
            options.infoWindow[name].apply(this, [e]);
          });
        }
      })(marker.infoWindow, info_window_events[ev]);
    }
  }

  var marker_events = ['animation_changed', 'clickable_changed', 'cursor_changed', 'draggable_changed', 'flat_changed', 'icon_changed', 'position_changed', 'shadow_changed', 'shape_changed', 'title_changed', 'visible_changed', 'zindex_changed'];

  var marker_events_with_mouse = ['dblclick', 'drag', 'dragend', 'dragstart', 'mousedown', 'mouseout', 'mouseover', 'mouseup'];

  for (var ev = 0; ev < marker_events.length; ev++) {
    (function(object, name) {
      if (options[name]) {
        google.maps.event.addListener(object, name, function(){
          options[name].apply(this, [this]);
        });
      }
    })(marker, marker_events[ev]);
  }

  for (var ev = 0; ev < marker_events_with_mouse.length; ev++) {
    (function(map, object, name) {
      if (options[name]) {
        google.maps.event.addListener(object, name, function(me){
          if(!me.pixel){
            me.pixel = map.getProjection().fromLatLngToPoint(me.latLng)
          }
          
          options[name].apply(this, [me]);
        });
      }
    })(this.map, marker, marker_events_with_mouse[ev]);
  }

  google.maps.event.addListener(marker, 'click', function() {
    this.details = details;

    if (options.click) {
      options.click.apply(this, [this]);
    }

    if (marker.infoWindow) {
      self.hideInfoWindows();
      marker.infoWindow.open(self.map, marker);
    }
  });

  google.maps.event.addListener(marker, 'rightclick', function(e) {
    e.marker = this;

    if (options.rightclick) {
      options.rightclick.apply(this, [e]);
    }

    if (window.context_menu[self.el.id]['marker'] != undefined) {
      self.buildContextMenu('marker', e);
    }
  });

  if (marker.fences) {
    google.maps.event.addListener(marker, 'dragend', function() {
      self.checkMarkerGeofence(marker, function(m, f) {
        outside(m, f);
      });
    });
  }

  return marker;
};

GMaps.prototype.addMarker = function(options) {
  var marker;
  if(options.hasOwnProperty('gm_accessors_')) {
    // Native google.maps.Marker object
    marker = options;
  }
  else {
    if ((options.hasOwnProperty('lat') && options.hasOwnProperty('lng')) || options.position) {
      marker = this.createMarker(options);
    }
    else {
      throw 'No latitude or longitude defined.';
    }
  }

  marker.setMap(this.map);

  if(this.markerClusterer) {
    this.markerClusterer.addMarker(marker);
  }

  this.markers.push(marker);

  GMaps.fire('marker_added', marker, this);

  return marker;
};

GMaps.prototype.addMarkers = function(array) {
  for (var i = 0, marker; marker=array[i]; i++) {
    this.addMarker(marker);
  }

  return this.markers;
};

GMaps.prototype.hideInfoWindows = function() {
  for (var i = 0, marker; marker = this.markers[i]; i++){
    if (marker.infoWindow) {
      marker.infoWindow.close();
    }
  }
};

GMaps.prototype.removeMarker = function(marker) {
  for (var i = 0; i < this.markers.length; i++) {
    if (this.markers[i] === marker) {
      this.markers[i].setMap(null);
      this.markers.splice(i, 1);

      if(this.markerClusterer) {
        this.markerClusterer.removeMarker(marker);
      }

      GMaps.fire('marker_removed', marker, this);

      break;
    }
  }

  return marker;
};

GMaps.prototype.removeMarkers = function (collection) {
  var new_markers = [];

  if (typeof collection == 'undefined') {
    for (var i = 0; i < this.markers.length; i++) {
      var marker = this.markers[i];
      marker.setMap(null);

      if(this.markerClusterer) {
        this.markerClusterer.removeMarker(marker);
      }

      GMaps.fire('marker_removed', marker, this);
    }
    
    this.markers = new_markers;
  }
  else {
    for (var i = 0; i < collection.length; i++) {
      var index = this.markers.indexOf(collection[i]);

      if (index > -1) {
        var marker = this.markers[index];
        marker.setMap(null);

        if(this.markerClusterer) {
          this.markerClusterer.removeMarker(marker);
        }

        GMaps.fire('marker_removed', marker, this);
      }
    }

    for (var i = 0; i < this.markers.length; i++) {
      var marker = this.markers[i];
      if (marker.getMap() != null) {
        new_markers.push(marker);
      }
    }

    this.markers = new_markers;
  }
};

GMaps.prototype.drawOverlay = function(options) {
  var overlay = new google.maps.OverlayView(),
      auto_show = true;

  overlay.setMap(this.map);

  if (options.auto_show != null) {
    auto_show = options.auto_show;
  }

  overlay.onAdd = function() {
    var el = document.createElement('div');

    el.style.borderStyle = "none";
    el.style.borderWidth = "0px";
    el.style.position = "absolute";
    el.style.zIndex = 100;
    el.innerHTML = options.content;

    overlay.el = el;

    if (!options.layer) {
      options.layer = 'overlayLayer';
    }
    
    var panes = this.getPanes(),
        overlayLayer = panes[options.layer],
        stop_overlay_events = ['contextmenu', 'DOMMouseScroll', 'dblclick', 'mousedown'];

    overlayLayer.appendChild(el);

    for (var ev = 0; ev < stop_overlay_events.length; ev++) {
      (function(object, name) {
        google.maps.event.addDomListener(object, name, function(e){
          if (navigator.userAgent.toLowerCase().indexOf('msie') != -1 && document.all) {
            e.cancelBubble = true;
            e.returnValue = false;
          }
          else {
            e.stopPropagation();
          }
        });
      })(el, stop_overlay_events[ev]);
    }

    if (options.click) {
      panes.overlayMouseTarget.appendChild(overlay.el);
      google.maps.event.addDomListener(overlay.el, 'click', function() {
        options.click.apply(overlay, [overlay]);
      });
    }

    google.maps.event.trigger(this, 'ready');
  };

  overlay.draw = function() {
    var projection = this.getProjection(),
        pixel = projection.fromLatLngToDivPixel(new google.maps.LatLng(options.lat, options.lng));

    options.horizontalOffset = options.horizontalOffset || 0;
    options.verticalOffset = options.verticalOffset || 0;

    var el = overlay.el,
        content = el.children[0],
        content_height = content.clientHeight,
        content_width = content.clientWidth;

    switch (options.verticalAlign) {
      case 'top':
        el.style.top = (pixel.y - content_height + options.verticalOffset) + 'px';
        break;
      default:
      case 'middle':
        el.style.top = (pixel.y - (content_height / 2) + options.verticalOffset) + 'px';
        break;
      case 'bottom':
        el.style.top = (pixel.y + options.verticalOffset) + 'px';
        break;
    }

    switch (options.horizontalAlign) {
      case 'left':
        el.style.left = (pixel.x - content_width + options.horizontalOffset) + 'px';
        break;
      default:
      case 'center':
        el.style.left = (pixel.x - (content_width / 2) + options.horizontalOffset) + 'px';
        break;
      case 'right':
        el.style.left = (pixel.x + options.horizontalOffset) + 'px';
        break;
    }

    el.style.display = auto_show ? 'block' : 'none';

    if (!auto_show) {
      options.show.apply(this, [el]);
    }
  };

  overlay.onRemove = function() {
    var el = overlay.el;

    if (options.remove) {
      options.remove.apply(this, [el]);
    }
    else {
      overlay.el.parentNode.removeChild(overlay.el);
      overlay.el = null;
    }
  };

  this.overlays.push(overlay);
  return overlay;
};

GMaps.prototype.removeOverlay = function(overlay) {
  for (var i = 0; i < this.overlays.length; i++) {
    if (this.overlays[i] === overlay) {
      this.overlays[i].setMap(null);
      this.overlays.splice(i, 1);

      break;
    }
  }
};

GMaps.prototype.removeOverlays = function() {
  for (var i = 0, item; item = this.overlays[i]; i++) {
    item.setMap(null);
  }

  this.overlays = [];
};

GMaps.prototype.drawPolyline = function(options) {
  var path = [],
      points = options.path;

  if (points.length) {
    if (points[0][0] === undefined) {
      path = points;
    }
    else {
      for (var i = 0, latlng; latlng = points[i]; i++) {
        path.push(new google.maps.LatLng(latlng[0], latlng[1]));
      }
    }
  }

  var polyline_options = {
    map: this.map,
    path: path,
    strokeColor: options.strokeColor,
    strokeOpacity: options.strokeOpacity,
    strokeWeight: options.strokeWeight,
    geodesic: options.geodesic,
    clickable: true,
    editable: false,
    visible: true
  };

  if (options.hasOwnProperty("clickable")) {
    polyline_options.clickable = options.clickable;
  }

  if (options.hasOwnProperty("editable")) {
    polyline_options.editable = options.editable;
  }

  if (options.hasOwnProperty("icons")) {
    polyline_options.icons = options.icons;
  }

  if (options.hasOwnProperty("zIndex")) {
    polyline_options.zIndex = options.zIndex;
  }

  var polyline = new google.maps.Polyline(polyline_options);

  var polyline_events = ['click', 'dblclick', 'mousedown', 'mousemove', 'mouseout', 'mouseover', 'mouseup', 'rightclick'];

  for (var ev = 0; ev < polyline_events.length; ev++) {
    (function(object, name) {
      if (options[name]) {
        google.maps.event.addListener(object, name, function(e){
          options[name].apply(this, [e]);
        });
      }
    })(polyline, polyline_events[ev]);
  }

  this.polylines.push(polyline);

  GMaps.fire('polyline_added', polyline, this);

  return polyline;
};

GMaps.prototype.removePolyline = function(polyline) {
  for (var i = 0; i < this.polylines.length; i++) {
    if (this.polylines[i] === polyline) {
      this.polylines[i].setMap(null);
      this.polylines.splice(i, 1);

      GMaps.fire('polyline_removed', polyline, this);

      break;
    }
  }
};

GMaps.prototype.removePolylines = function() {
  for (var i = 0, item; item = this.polylines[i]; i++) {
    item.setMap(null);
  }

  this.polylines = [];
};

GMaps.prototype.drawCircle = function(options) {
  options =  extend_object({
    map: this.map,
    center: new google.maps.LatLng(options.lat, options.lng)
  }, options);

  delete options.lat;
  delete options.lng;

  var polygon = new google.maps.Circle(options),
      polygon_events = ['click', 'dblclick', 'mousedown', 'mousemove', 'mouseout', 'mouseover', 'mouseup', 'rightclick'];

  for (var ev = 0; ev < polygon_events.length; ev++) {
    (function(object, name) {
      if (options[name]) {
        google.maps.event.addListener(object, name, function(e){
          options[name].apply(this, [e]);
        });
      }
    })(polygon, polygon_events[ev]);
  }

  this.polygons.push(polygon);

  return polygon;
};

GMaps.prototype.drawRectangle = function(options) {
  options = extend_object({
    map: this.map
  }, options);

  var latLngBounds = new google.maps.LatLngBounds(
    new google.maps.LatLng(options.bounds[0][0], options.bounds[0][1]),
    new google.maps.LatLng(options.bounds[1][0], options.bounds[1][1])
  );

  options.bounds = latLngBounds;

  var polygon = new google.maps.Rectangle(options),
      polygon_events = ['click', 'dblclick', 'mousedown', 'mousemove', 'mouseout', 'mouseover', 'mouseup', 'rightclick'];

  for (var ev = 0; ev < polygon_events.length; ev++) {
    (function(object, name) {
      if (options[name]) {
        google.maps.event.addListener(object, name, function(e){
          options[name].apply(this, [e]);
        });
      }
    })(polygon, polygon_events[ev]);
  }

  this.polygons.push(polygon);

  return polygon;
};

GMaps.prototype.drawPolygon = function(options) {
  var useGeoJSON = false;

  if(options.hasOwnProperty("useGeoJSON")) {
    useGeoJSON = options.useGeoJSON;
  }

  delete options.useGeoJSON;

  options = extend_object({
    map: this.map
  }, options);

  if (useGeoJSON == false) {
    options.paths = [options.paths.slice(0)];
  }

  if (options.paths.length > 0) {
    if (options.paths[0].length > 0) {
      options.paths = array_flat(array_map(options.paths, arrayToLatLng, useGeoJSON));
    }
  }

  var polygon = new google.maps.Polygon(options),
      polygon_events = ['click', 'dblclick', 'mousedown', 'mousemove', 'mouseout', 'mouseover', 'mouseup', 'rightclick'];

  for (var ev = 0; ev < polygon_events.length; ev++) {
    (function(object, name) {
      if (options[name]) {
        google.maps.event.addListener(object, name, function(e){
          options[name].apply(this, [e]);
        });
      }
    })(polygon, polygon_events[ev]);
  }

  this.polygons.push(polygon);

  GMaps.fire('polygon_added', polygon, this);

  return polygon;
};

GMaps.prototype.removePolygon = function(polygon) {
  for (var i = 0; i < this.polygons.length; i++) {
    if (this.polygons[i] === polygon) {
      this.polygons[i].setMap(null);
      this.polygons.splice(i, 1);

      GMaps.fire('polygon_removed', polygon, this);

      break;
    }
  }
};

GMaps.prototype.removePolygons = function() {
  for (var i = 0, item; item = this.polygons[i]; i++) {
    item.setMap(null);
  }

  this.polygons = [];
};

GMaps.prototype.getFromFusionTables = function(options) {
  var events = options.events;

  delete options.events;

  var fusion_tables_options = options,
      layer = new google.maps.FusionTablesLayer(fusion_tables_options);

  for (var ev in events) {
    (function(object, name) {
      google.maps.event.addListener(object, name, function(e) {
        events[name].apply(this, [e]);
      });
    })(layer, ev);
  }

  this.layers.push(layer);

  return layer;
};

GMaps.prototype.loadFromFusionTables = function(options) {
  var layer = this.getFromFusionTables(options);
  layer.setMap(this.map);

  return layer;
};

GMaps.prototype.getFromKML = function(options) {
  var url = options.url,
      events = options.events;

  delete options.url;
  delete options.events;

  var kml_options = options,
      layer = new google.maps.KmlLayer(url, kml_options);

  for (var ev in events) {
    (function(object, name) {
      google.maps.event.addListener(object, name, function(e) {
        events[name].apply(this, [e]);
      });
    })(layer, ev);
  }

  this.layers.push(layer);

  return layer;
};

GMaps.prototype.loadFromKML = function(options) {
  var layer = this.getFromKML(options);
  layer.setMap(this.map);

  return layer;
};

GMaps.prototype.addLayer = function(layerName, options) {
  //var default_layers = ['weather', 'clouds', 'traffic', 'transit', 'bicycling', 'panoramio', 'places'];
  options = options || {};
  var layer;

  switch(layerName) {
    case 'weather': this.singleLayers.weather = layer = new google.maps.weather.WeatherLayer();
      break;
    case 'clouds': this.singleLayers.clouds = layer = new google.maps.weather.CloudLayer();
      break;
    case 'traffic': this.singleLayers.traffic = layer = new google.maps.TrafficLayer();
      break;
    case 'transit': this.singleLayers.transit = layer = new google.maps.TransitLayer();
      break;
    case 'bicycling': this.singleLayers.bicycling = layer = new google.maps.BicyclingLayer();
      break;
    case 'panoramio':
        this.singleLayers.panoramio = layer = new google.maps.panoramio.PanoramioLayer();
        layer.setTag(options.filter);
        delete options.filter;

        //click event
        if (options.click) {
          google.maps.event.addListener(layer, 'click', function(event) {
            options.click(event);
            delete options.click;
          });
        }
      break;
      case 'places':
        this.singleLayers.places = layer = new google.maps.places.PlacesService(this.map);

        //search, nearbySearch, radarSearch callback, Both are the same
        if (options.search || options.nearbySearch || options.radarSearch) {
          var placeSearchRequest  = {
            bounds : options.bounds || null,
            keyword : options.keyword || null,
            location : options.location || null,
            name : options.name || null,
            radius : options.radius || null,
            rankBy : options.rankBy || null,
            types : options.types || null
          };

          if (options.radarSearch) {
            layer.radarSearch(placeSearchRequest, options.radarSearch);
          }

          if (options.search) {
            layer.search(placeSearchRequest, options.search);
          }

          if (options.nearbySearch) {
            layer.nearbySearch(placeSearchRequest, options.nearbySearch);
          }
        }

        //textSearch callback
        if (options.textSearch) {
          var textSearchRequest  = {
            bounds : options.bounds || null,
            location : options.location || null,
            query : options.query || null,
            radius : options.radius || null
          };

          layer.textSearch(textSearchRequest, options.textSearch);
        }
      break;
  }

  if (layer !== undefined) {
    if (typeof layer.setOptions == 'function') {
      layer.setOptions(options);
    }
    if (typeof layer.setMap == 'function') {
      layer.setMap(this.map);
    }

    return layer;
  }
};

GMaps.prototype.removeLayer = function(layer) {
  if (typeof(layer) == "string" && this.singleLayers[layer] !== undefined) {
     this.singleLayers[layer].setMap(null);

     delete this.singleLayers[layer];
  }
  else {
    for (var i = 0; i < this.layers.length; i++) {
      if (this.layers[i] === layer) {
        this.layers[i].setMap(null);
        this.layers.splice(i, 1);

        break;
      }
    }
  }
};

var travelMode, unitSystem;

GMaps.prototype.getRoutes = function(options) {
  switch (options.travelMode) {
    case 'bicycling':
      travelMode = google.maps.TravelMode.BICYCLING;
      break;
    case 'transit':
      travelMode = google.maps.TravelMode.TRANSIT;
      break;
    case 'driving':
      travelMode = google.maps.TravelMode.DRIVING;
      break;
    default:
      travelMode = google.maps.TravelMode.WALKING;
      break;
  }

  if (options.unitSystem === 'imperial') {
    unitSystem = google.maps.UnitSystem.IMPERIAL;
  }
  else {
    unitSystem = google.maps.UnitSystem.METRIC;
  }

  var base_options = {
        avoidHighways: false,
        avoidTolls: false,
        optimizeWaypoints: false,
        waypoints: []
      },
      request_options =  extend_object(base_options, options);

  request_options.origin = /string/.test(typeof options.origin) ? options.origin : new google.maps.LatLng(options.origin[0], options.origin[1]);
  request_options.destination = /string/.test(typeof options.destination) ? options.destination : new google.maps.LatLng(options.destination[0], options.destination[1]);
  request_options.travelMode = travelMode;
  request_options.unitSystem = unitSystem;

  delete request_options.callback;
  delete request_options.error;

  var self = this,
      service = new google.maps.DirectionsService();

  service.route(request_options, function(result, status) {
    if (status === google.maps.DirectionsStatus.OK) {
      for (var r in result.routes) {
        if (result.routes.hasOwnProperty(r)) {
          self.routes.push(result.routes[r]);
        }
      }

      if (options.callback) {
        options.callback(self.routes);
      }
    }
    else {
      if (options.error) {
        options.error(result, status);
      }
    }
  });
};

GMaps.prototype.removeRoutes = function() {
  this.routes = [];
};

GMaps.prototype.getElevations = function(options) {
  options = extend_object({
    locations: [],
    path : false,
    samples : 256
  }, options);

  if (options.locations.length > 0) {
    if (options.locations[0].length > 0) {
      options.locations = array_flat(array_map([options.locations], arrayToLatLng,  false));
    }
  }

  var callback = options.callback;
  delete options.callback;

  var service = new google.maps.ElevationService();

  //location request
  if (!options.path) {
    delete options.path;
    delete options.samples;

    service.getElevationForLocations(options, function(result, status) {
      if (callback && typeof(callback) === "function") {
        callback(result, status);
      }
    });
  //path request
  } else {
    var pathRequest = {
      path : options.locations,
      samples : options.samples
    };

    service.getElevationAlongPath(pathRequest, function(result, status) {
     if (callback && typeof(callback) === "function") {
        callback(result, status);
      }
    });
  }
};

GMaps.prototype.cleanRoute = GMaps.prototype.removePolylines;

GMaps.prototype.drawRoute = function(options) {
  var self = this;

  this.getRoutes({
    origin: options.origin,
    destination: options.destination,
    travelMode: options.travelMode,
    waypoints: options.waypoints,
    unitSystem: options.unitSystem,
    error: options.error,
    callback: function(e) {
      if (e.length > 0) {
        var polyline_options = {
          path: e[e.length - 1].overview_path,
          strokeColor: options.strokeColor,
          strokeOpacity: options.strokeOpacity,
          strokeWeight: options.strokeWeight
        };

        if (options.hasOwnProperty("icons")) {
          polyline_options.icons = options.icons;
        }

        self.drawPolyline(polyline_options);
        
        if (options.callback) {
          options.callback(e[e.length - 1]);
        }
      }
    }
  });
};

GMaps.prototype.travelRoute = function(options) {
  if (options.origin && options.destination) {
    this.getRoutes({
      origin: options.origin,
      destination: options.destination,
      travelMode: options.travelMode,
      waypoints : options.waypoints,
      unitSystem: options.unitSystem,
      error: options.error,
      callback: function(e) {
        //start callback
        if (e.length > 0 && options.start) {
          options.start(e[e.length - 1]);
        }

        //step callback
        if (e.length > 0 && options.step) {
          var route = e[e.length - 1];
          if (route.legs.length > 0) {
            var steps = route.legs[0].steps;
            for (var i = 0, step; step = steps[i]; i++) {
              step.step_number = i;
              options.step(step, (route.legs[0].steps.length - 1));
            }
          }
        }

        //end callback
        if (e.length > 0 && options.end) {
           options.end(e[e.length - 1]);
        }
      }
    });
  }
  else if (options.route) {
    if (options.route.legs.length > 0) {
      var steps = options.route.legs[0].steps;
      for (var i = 0, step; step = steps[i]; i++) {
        step.step_number = i;
        options.step(step);
      }
    }
  }
};

GMaps.prototype.drawSteppedRoute = function(options) {
  var self = this;

  if (options.origin && options.destination) {
    this.getRoutes({
      origin: options.origin,
      destination: options.destination,
      travelMode: options.travelMode,
      waypoints : options.waypoints,
      error: options.error,
      callback: function(e) {
        //start callback
        if (e.length > 0 && options.start) {
          options.start(e[e.length - 1]);
        }

        //step callback
        if (e.length > 0 && options.step) {
          var route = e[e.length - 1];
          if (route.legs.length > 0) {
            var steps = route.legs[0].steps;
            for (var i = 0, step; step = steps[i]; i++) {
              step.step_number = i;
              var polyline_options = {
                path: step.path,
                strokeColor: options.strokeColor,
                strokeOpacity: options.strokeOpacity,
                strokeWeight: options.strokeWeight
              };

              if (options.hasOwnProperty("icons")) {
                polyline_options.icons = options.icons;
              }

              self.drawPolyline(polyline_options);
              options.step(step, (route.legs[0].steps.length - 1));
            }
          }
        }

        //end callback
        if (e.length > 0 && options.end) {
           options.end(e[e.length - 1]);
        }
      }
    });
  }
  else if (options.route) {
    if (options.route.legs.length > 0) {
      var steps = options.route.legs[0].steps;
      for (var i = 0, step; step = steps[i]; i++) {
        step.step_number = i;
        var polyline_options = {
          path: step.path,
          strokeColor: options.strokeColor,
          strokeOpacity: options.strokeOpacity,
          strokeWeight: options.strokeWeight
        };

        if (options.hasOwnProperty("icons")) {
          polyline_options.icons = options.icons;
        }

        self.drawPolyline(polyline_options);
        options.step(step);
      }
    }
  }
};

GMaps.Route = function(options) {
  this.origin = options.origin;
  this.destination = options.destination;
  this.waypoints = options.waypoints;

  this.map = options.map;
  this.route = options.route;
  this.step_count = 0;
  this.steps = this.route.legs[0].steps;
  this.steps_length = this.steps.length;

  var polyline_options = {
    path: new google.maps.MVCArray(),
    strokeColor: options.strokeColor,
    strokeOpacity: options.strokeOpacity,
    strokeWeight: options.strokeWeight
  };

  if (options.hasOwnProperty("icons")) {
    polyline_options.icons = options.icons;
  }

  this.polyline = this.map.drawPolyline(polyline_options).getPath();
};

GMaps.Route.prototype.getRoute = function(options) {
  var self = this;

  this.map.getRoutes({
    origin : this.origin,
    destination : this.destination,
    travelMode : options.travelMode,
    waypoints : this.waypoints || [],
    error: options.error,
    callback : function() {
      self.route = e[0];

      if (options.callback) {
        options.callback.call(self);
      }
    }
  });
};

GMaps.Route.prototype.back = function() {
  if (this.step_count > 0) {
    this.step_count--;
    var path = this.route.legs[0].steps[this.step_count].path;

    for (var p in path){
      if (path.hasOwnProperty(p)){
        this.polyline.pop();
      }
    }
  }
};

GMaps.Route.prototype.forward = function() {
  if (this.step_count < this.steps_length) {
    var path = this.route.legs[0].steps[this.step_count].path;

    for (var p in path){
      if (path.hasOwnProperty(p)){
        this.polyline.push(path[p]);
      }
    }
    this.step_count++;
  }
};

GMaps.prototype.checkGeofence = function(lat, lng, fence) {
  return fence.containsLatLng(new google.maps.LatLng(lat, lng));
};

GMaps.prototype.checkMarkerGeofence = function(marker, outside_callback) {
  if (marker.fences) {
    for (var i = 0, fence; fence = marker.fences[i]; i++) {
      var pos = marker.getPosition();
      if (!this.checkGeofence(pos.lat(), pos.lng(), fence)) {
        outside_callback(marker, fence);
      }
    }
  }
};

GMaps.prototype.toImage = function(options) {
  var options = options || {},
      static_map_options = {};

  static_map_options['size'] = options['size'] || [this.el.clientWidth, this.el.clientHeight];
  static_map_options['lat'] = this.getCenter().lat();
  static_map_options['lng'] = this.getCenter().lng();

  if (this.markers.length > 0) {
    static_map_options['markers'] = [];
    
    for (var i = 0; i < this.markers.length; i++) {
      static_map_options['markers'].push({
        lat: this.markers[i].getPosition().lat(),
        lng: this.markers[i].getPosition().lng()
      });
    }
  }

  if (this.polylines.length > 0) {
    var polyline = this.polylines[0];
    
    static_map_options['polyline'] = {};
    static_map_options['polyline']['path'] = google.maps.geometry.encoding.encodePath(polyline.getPath());
    static_map_options['polyline']['strokeColor'] = polyline.strokeColor
    static_map_options['polyline']['strokeOpacity'] = polyline.strokeOpacity
    static_map_options['polyline']['strokeWeight'] = polyline.strokeWeight
  }

  return GMaps.staticMapURL(static_map_options);
};

GMaps.staticMapURL = function(options){
  var parameters = [],
      data,
      static_root = (location.protocol === 'file:' ? 'http:' : location.protocol ) + '//maps.googleapis.com/maps/api/staticmap';

  if (options.url) {
    static_root = options.url;
    delete options.url;
  }

  static_root += '?';

  var markers = options.markers;
  
  delete options.markers;

  if (!markers && options.marker) {
    markers = [options.marker];
    delete options.marker;
  }

  var styles = options.styles;

  delete options.styles;

  var polyline = options.polyline;
  delete options.polyline;

  /** Map options **/
  if (options.center) {
    parameters.push('center=' + options.center);
    delete options.center;
  }
  else if (options.address) {
    parameters.push('center=' + options.address);
    delete options.address;
  }
  else if (options.lat) {
    parameters.push(['center=', options.lat, ',', options.lng].join(''));
    delete options.lat;
    delete options.lng;
  }
  else if (options.visible) {
    var visible = encodeURI(options.visible.join('|'));
    parameters.push('visible=' + visible);
  }

  var size = options.size;
  if (size) {
    if (size.join) {
      size = size.join('x');
    }
    delete options.size;
  }
  else {
    size = '630x300';
  }
  parameters.push('size=' + size);

  if (!options.zoom && options.zoom !== false) {
    options.zoom = 15;
  }

  var sensor = options.hasOwnProperty('sensor') ? !!options.sensor : true;
  delete options.sensor;
  parameters.push('sensor=' + sensor);

  for (var param in options) {
    if (options.hasOwnProperty(param)) {
      parameters.push(param + '=' + options[param]);
    }
  }

  /** Markers **/
  if (markers) {
    var marker, loc;

    for (var i = 0; data = markers[i]; i++) {
      marker = [];

      if (data.size && data.size !== 'normal') {
        marker.push('size:' + data.size);
        delete data.size;
      }
      else if (data.icon) {
        marker.push('icon:' + encodeURI(data.icon));
        delete data.icon;
      }

      if (data.color) {
        marker.push('color:' + data.color.replace('#', '0x'));
        delete data.color;
      }

      if (data.label) {
        marker.push('label:' + data.label[0].toUpperCase());
        delete data.label;
      }

      loc = (data.address ? data.address : data.lat + ',' + data.lng);
      delete data.address;
      delete data.lat;
      delete data.lng;

      for(var param in data){
        if (data.hasOwnProperty(param)) {
          marker.push(param + ':' + data[param]);
        }
      }

      if (marker.length || i === 0) {
        marker.push(loc);
        marker = marker.join('|');
        parameters.push('markers=' + encodeURI(marker));
      }
      // New marker without styles
      else {
        marker = parameters.pop() + encodeURI('|' + loc);
        parameters.push(marker);
      }
    }
  }

  /** Map Styles **/
  if (styles) {
    for (var i = 0; i < styles.length; i++) {
      var styleRule = [];
      if (styles[i].featureType){
        styleRule.push('feature:' + styles[i].featureType.toLowerCase());
      }

      if (styles[i].elementType) {
        styleRule.push('element:' + styles[i].elementType.toLowerCase());
      }

      for (var j = 0; j < styles[i].stylers.length; j++) {
        for (var p in styles[i].stylers[j]) {
          var ruleArg = styles[i].stylers[j][p];
          if (p == 'hue' || p == 'color') {
            ruleArg = '0x' + ruleArg.substring(1);
          }
          styleRule.push(p + ':' + ruleArg);
        }
      }

      var rule = styleRule.join('|');
      if (rule != '') {
        parameters.push('style=' + rule);
      }
    }
  }

  /** Polylines **/
  function parseColor(color, opacity) {
    if (color[0] === '#'){
      color = color.replace('#', '0x');

      if (opacity) {
        opacity = parseFloat(opacity);
        opacity = Math.min(1, Math.max(opacity, 0));
        if (opacity === 0) {
          return '0x00000000';
        }
        opacity = (opacity * 255).toString(16);
        if (opacity.length === 1) {
          opacity += opacity;
        }

        color = color.slice(0,8) + opacity;
      }
    }
    return color;
  }

  if (polyline) {
    data = polyline;
    polyline = [];

    if (data.strokeWeight) {
      polyline.push('weight:' + parseInt(data.strokeWeight, 10));
    }

    if (data.strokeColor) {
      var color = parseColor(data.strokeColor, data.strokeOpacity);
      polyline.push('color:' + color);
    }

    if (data.fillColor) {
      var fillcolor = parseColor(data.fillColor, data.fillOpacity);
      polyline.push('fillcolor:' + fillcolor);
    }

    var path = data.path;
    if (path.join) {
      for (var j=0, pos; pos=path[j]; j++) {
        polyline.push(pos.join(','));
      }
    }
    else {
      polyline.push('enc:' + path);
    }

    polyline = polyline.join('|');
    parameters.push('path=' + encodeURI(polyline));
  }

  /** Retina support **/
  var dpi = window.devicePixelRatio || 1;
  parameters.push('scale=' + dpi);

  parameters = parameters.join('&');
  return static_root + parameters;
};

GMaps.prototype.addMapType = function(mapTypeId, options) {
  if (options.hasOwnProperty("getTileUrl") && typeof(options["getTileUrl"]) == "function") {
    options.tileSize = options.tileSize || new google.maps.Size(256, 256);

    var mapType = new google.maps.ImageMapType(options);

    this.map.mapTypes.set(mapTypeId, mapType);
  }
  else {
    throw "'getTileUrl' function required.";
  }
};

GMaps.prototype.addOverlayMapType = function(options) {
  if (options.hasOwnProperty("getTile") && typeof(options["getTile"]) == "function") {
    var overlayMapTypeIndex = options.index;

    delete options.index;

    this.map.overlayMapTypes.insertAt(overlayMapTypeIndex, options);
  }
  else {
    throw "'getTile' function required.";
  }
};

GMaps.prototype.removeOverlayMapType = function(overlayMapTypeIndex) {
  this.map.overlayMapTypes.removeAt(overlayMapTypeIndex);
};

GMaps.prototype.addStyle = function(options) {
  var styledMapType = new google.maps.StyledMapType(options.styles, { name: options.styledMapName });

  this.map.mapTypes.set(options.mapTypeId, styledMapType);
};

GMaps.prototype.setStyle = function(mapTypeId) {
  this.map.setMapTypeId(mapTypeId);
};

GMaps.prototype.createPanorama = function(streetview_options) {
  if (!streetview_options.hasOwnProperty('lat') || !streetview_options.hasOwnProperty('lng')) {
    streetview_options.lat = this.getCenter().lat();
    streetview_options.lng = this.getCenter().lng();
  }

  this.panorama = GMaps.createPanorama(streetview_options);

  this.map.setStreetView(this.panorama);

  return this.panorama;
};

GMaps.createPanorama = function(options) {
  var el = getElementById(options.el, options.context);

  var panoramaService = new google.maps.StreetViewService();
  var checkaround = options.checkaround || 50;
  var panorama = null;

  options.position = new google.maps.LatLng(options.lat, options.lng);

  delete options.el;
  delete options.context;
  delete options.lat;
  delete options.lng;
  delete options.checkaround;

  var streetview_events = ['closeclick', 'links_changed', 'pano_changed', 'position_changed', 'pov_changed', 'resize', 'visible_changed'],
      streetview_options = extend_object({visible : true}, options);

  for (var i = 0; i < streetview_events.length; i++) {
    delete streetview_options[streetview_events[i]];
  }

  //get only a streetview if this one is available
  panoramaService.getPanoramaByLocation(options.position, checkaround ,function(data, status){
    if (status == google.maps.StreetViewStatus.OK) {

      streetview_options.position = data.location.latLng;

      panorama = new google.maps.StreetViewPanorama(el, streetview_options);

      for (var i = 0; i < streetview_events.length; i++) {
        (function(object, name) {
          if (options[name]) {
            google.maps.event.addListener(object, name, function(){
              options[name].apply(this);
            });
          }
        })(panorama, streetview_events[i]);
      }
      panorama.setVisible(true);
      return panorama;
    // no result
    } else {
      return false;
    }
  });
};
GMaps.prototype.on = function(event_name, handler) {
  return GMaps.on(event_name, this, handler);
};

GMaps.prototype.off = function(event_name) {
  GMaps.off(event_name, this);
};

GMaps.custom_events = ['marker_added', 'marker_removed', 'polyline_added', 'polyline_removed', 'polygon_added', 'polygon_removed', 'geolocated', 'geolocation_failed'];

GMaps.on = function(event_name, object, handler) {
  if (GMaps.custom_events.indexOf(event_name) == -1) {
    if(object instanceof GMaps) object = object.map; 
    return google.maps.event.addListener(object, event_name, handler);
  }
  else {
    var registered_event = {
      handler : handler,
      eventName : event_name
    };

    object.registered_events[event_name] = object.registered_events[event_name] || [];
    object.registered_events[event_name].push(registered_event);

    return registered_event;
  }
};

GMaps.off = function(event_name, object) {
  if (GMaps.custom_events.indexOf(event_name) == -1) {
    if(object instanceof GMaps) object = object.map; 
    google.maps.event.clearListeners(object, event_name);
  }
  else {
    object.registered_events[event_name] = [];
  }
};

GMaps.fire = function(event_name, object, scope) {
  if (GMaps.custom_events.indexOf(event_name) == -1) {
    google.maps.event.trigger(object, event_name, Array.prototype.slice.apply(arguments).slice(2));
  }
  else {
    if(event_name in scope.registered_events) {
      var firing_events = scope.registered_events[event_name];

      for(var i = 0; i < firing_events.length; i++) {
        (function(handler, scope, object) {
          handler.apply(scope, [object]);
        })(firing_events[i]['handler'], scope, object);
      }
    }
  }
};

GMaps.geolocate = function(options) {
  var complete_callback = options.always || options.complete;

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      options.success(position);

      if (complete_callback) {
        complete_callback();
      }
    }, function(error) {
      options.error(error);

      if (complete_callback) {
        complete_callback();
      }
    }, options.options);
  }
  else {
    options.not_supported();

    if (complete_callback) {
      complete_callback();
    }
  }
};

GMaps.geocode = function(options) {
  this.geocoder = new google.maps.Geocoder();
  var callback = options.callback;
  if (options.hasOwnProperty('lat') && options.hasOwnProperty('lng')) {
    options.latLng = new google.maps.LatLng(options.lat, options.lng);
  }

  delete options.lat;
  delete options.lng;
  delete options.callback;
  
  this.geocoder.geocode(options, function(results, status) {
    callback(results, status);
  });
};

//==========================
// Polygon containsLatLng
// https://github.com/tparkin/Google-Maps-Point-in-Polygon
// Poygon getBounds extension - google-maps-extensions
// http://code.google.com/p/google-maps-extensions/source/browse/google.maps.Polygon.getBounds.js
if (!google.maps.Polygon.prototype.getBounds) {
  google.maps.Polygon.prototype.getBounds = function(latLng) {
    var bounds = new google.maps.LatLngBounds();
    var paths = this.getPaths();
    var path;

    for (var p = 0; p < paths.getLength(); p++) {
      path = paths.getAt(p);
      for (var i = 0; i < path.getLength(); i++) {
        bounds.extend(path.getAt(i));
      }
    }

    return bounds;
  };
}

if (!google.maps.Polygon.prototype.containsLatLng) {
  // Polygon containsLatLng - method to determine if a latLng is within a polygon
  google.maps.Polygon.prototype.containsLatLng = function(latLng) {
    // Exclude points outside of bounds as there is no way they are in the poly
    var bounds = this.getBounds();

    if (bounds !== null && !bounds.contains(latLng)) {
      return false;
    }

    // Raycast point in polygon method
    var inPoly = false;

    var numPaths = this.getPaths().getLength();
    for (var p = 0; p < numPaths; p++) {
      var path = this.getPaths().getAt(p);
      var numPoints = path.getLength();
      var j = numPoints - 1;

      for (var i = 0; i < numPoints; i++) {
        var vertex1 = path.getAt(i);
        var vertex2 = path.getAt(j);

        if (vertex1.lng() < latLng.lng() && vertex2.lng() >= latLng.lng() || vertex2.lng() < latLng.lng() && vertex1.lng() >= latLng.lng()) {
          if (vertex1.lat() + (latLng.lng() - vertex1.lng()) / (vertex2.lng() - vertex1.lng()) * (vertex2.lat() - vertex1.lat()) < latLng.lat()) {
            inPoly = !inPoly;
          }
        }

        j = i;
      }
    }

    return inPoly;
  };
}

if (!google.maps.Circle.prototype.containsLatLng) {
  google.maps.Circle.prototype.containsLatLng = function(latLng) {
    if (google.maps.geometry) {
      return google.maps.geometry.spherical.computeDistanceBetween(this.getCenter(), latLng) <= this.getRadius();
    }
    else {
      return true;
    }
  };
}

google.maps.LatLngBounds.prototype.containsLatLng = function(latLng) {
  return this.contains(latLng);
};

google.maps.Marker.prototype.setFences = function(fences) {
  this.fences = fences;
};

google.maps.Marker.prototype.addFence = function(fence) {
  this.fences.push(fence);
};

google.maps.Marker.prototype.getId = function() {
  return this['__gm_id'];
};

//==========================
// Array indexOf
// https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/Array/indexOf
if (!Array.prototype.indexOf) {
  Array.prototype.indexOf = function (searchElement /*, fromIndex */ ) {
      "use strict";
      if (this == null) {
          throw new TypeError();
      }
      var t = Object(this);
      var len = t.length >>> 0;
      if (len === 0) {
          return -1;
      }
      var n = 0;
      if (arguments.length > 1) {
          n = Number(arguments[1]);
          if (n != n) { // shortcut for verifying if it's NaN
              n = 0;
          } else if (n != 0 && n != Infinity && n != -Infinity) {
              n = (n > 0 || -1) * Math.floor(Math.abs(n));
          }
      }
      if (n >= len) {
          return -1;
      }
      var k = n >= 0 ? n : Math.max(len - Math.abs(n), 0);
      for (; k < len; k++) {
          if (k in t && t[k] === searchElement) {
              return k;
          }
      }
      return -1;
  }
}
// ==ClosureCompiler==
// @compilation_level ADVANCED_OPTIMIZATIONS
// @externs_url https://raw.githubusercontent.com/google/closure-compiler/master/contrib/externs/maps/google_maps_api_v3.js
// ==/ClosureCompiler==

/**
 * @name MarkerClusterer for Google Maps v3
 * @version version 1.0
 * @author Luke Mahe
 * @fileoverview
 * The library creates and manages per-zoom-level clusters for large amounts of
 * markers.
 * <br/>
 * This is a v3 implementation of the
 * <a href="http://gmaps-utility-library-dev.googlecode.com/svn/tags/markerclusterer/"
 * >v2 MarkerClusterer</a>.
 */

/**
 * @license
 * Copyright 2010 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */


/**
 * A Marker Clusterer that clusters markers.
 *
 * @param {google.maps.Map} map The Google map to attach to.
 * @param {Array.<google.maps.Marker>=} opt_markers Optional markers to add to
 *   the cluster.
 * @param {Object=} opt_options support the following options:
 *     'gridSize': (number) The grid size of a cluster in pixels.
 *     'maxZoom': (number) The maximum zoom level that a marker can be part of a
 *                cluster.
 *     'zoomOnClick': (boolean) Whether the default behaviour of clicking on a
 *                    cluster is to zoom into it.
 *     'averageCenter': (boolean) Whether the center of each cluster should be
 *                      the average of all markers in the cluster.
 *     'minimumClusterSize': (number) The minimum number of markers to be in a
 *                           cluster before the markers are hidden and a count
 *                           is shown.
 *     'styles': (object) An object that has style properties:
 *       'url': (string) The image url.
 *       'height': (number) The image height.
 *       'width': (number) The image width.
 *       'anchor': (Array) The anchor position of the label text.
 *       'textColor': (string) The text color.
 *       'textSize': (number) The text size.
 *       'backgroundPosition': (string) The position of the backgound x, y.
 *       'iconAnchor': (Array) The anchor position of the icon x, y.
 * @constructor
 * @extends google.maps.OverlayView
 */
function MarkerClusterer(map, opt_markers, opt_options) {
  // MarkerClusterer implements google.maps.OverlayView interface. We use the
  // extend function to extend MarkerClusterer with google.maps.OverlayView
  // because it might not always be available when the code is defined so we
  // look for it at the last possible moment. If it doesn't exist now then
  // there is no point going ahead :)
  this.extend(MarkerClusterer, google.maps.OverlayView);
  this.map_ = map;

  /**
   * @type {Array.<google.maps.Marker>}
   * @private
   */
  this.markers_ = [];

  /**
   *  @type {Array.<Cluster>}
   */
  this.clusters_ = [];

  this.sizes = [53, 56, 66, 78, 90];

  /**
   * @private
   */
  this.styles_ = [];

  /**
   * @type {boolean}
   * @private
   */
  this.ready_ = false;

  var options = opt_options || {};

  /**
   * @type {number}
   * @private
   */
  this.gridSize_ = options['gridSize'] || 60;

  /**
   * @private
   */
  this.minClusterSize_ = options['minimumClusterSize'] || 2;


  /**
   * @type {?number}
   * @private
   */
  this.maxZoom_ = options['maxZoom'] || null;

  this.styles_ = options['styles'] || [];

  /**
   * @type {string}
   * @private
   */
  this.imagePath_ = options['imagePath'] ||
      this.MARKER_CLUSTER_IMAGE_PATH_;

  /**
   * @type {string}
   * @private
   */
  this.imageExtension_ = options['imageExtension'] ||
      this.MARKER_CLUSTER_IMAGE_EXTENSION_;

  /**
   * @type {boolean}
   * @private
   */
  this.zoomOnClick_ = true;

  if (options['zoomOnClick'] != undefined) {
    this.zoomOnClick_ = options['zoomOnClick'];
  }

  /**
   * @type {boolean}
   * @private
   */
  this.averageCenter_ = false;

  if (options['averageCenter'] != undefined) {
    this.averageCenter_ = options['averageCenter'];
  }

  this.setupStyles_();

  this.setMap(map);

  /**
   * @type {number}
   * @private
   */
  this.prevZoom_ = this.map_.getZoom();

  // Add the map event listeners
  var that = this;
  google.maps.event.addListener(this.map_, 'zoom_changed', function() {
    var zoom = that.map_.getZoom();

    if (that.prevZoom_ != zoom) {
      that.prevZoom_ = zoom;
      that.resetViewport();
    }
  });

  google.maps.event.addListener(this.map_, 'idle', function() {
    that.redraw();
  });

  // Finally, add the markers
  if (opt_markers && opt_markers.length) {
    this.addMarkers(opt_markers, false);
  }
}


/**
 * The marker cluster image path.
 *
 * @type {string}
 * @private
 */
MarkerClusterer.prototype.MARKER_CLUSTER_IMAGE_PATH_ = '../images/m';


/**
 * The marker cluster image path.
 *
 * @type {string}
 * @private
 */
MarkerClusterer.prototype.MARKER_CLUSTER_IMAGE_EXTENSION_ = 'png';


/**
 * Extends a objects prototype by anothers.
 *
 * @param {Object} obj1 The object to be extended.
 * @param {Object} obj2 The object to extend with.
 * @return {Object} The new extended object.
 * @ignore
 */
MarkerClusterer.prototype.extend = function(obj1, obj2) {
  return (function(object) {
    for (var property in object.prototype) {
      this.prototype[property] = object.prototype[property];
    }
    return this;
  }).apply(obj1, [obj2]);
};


/**
 * Implementaion of the interface method.
 * @ignore
 */
MarkerClusterer.prototype.onAdd = function() {
  this.setReady_(true);
};

/**
 * Implementaion of the interface method.
 * @ignore
 */
MarkerClusterer.prototype.draw = function() {};

/**
 * Sets up the styles object.
 *
 * @private
 */
MarkerClusterer.prototype.setupStyles_ = function() {
  if (this.styles_.length) {
    return;
  }

  for (var i = 0, size; size = this.sizes[i]; i++) {
    this.styles_.push({
      url: this.imagePath_ + (i + 1) + '.' + this.imageExtension_,
      height: size,
      width: size
    });
  }
};

/**
 *  Fit the map to the bounds of the markers in the clusterer.
 */
MarkerClusterer.prototype.fitMapToMarkers = function() {
  var markers = this.getMarkers();
  var bounds = new google.maps.LatLngBounds();
  for (var i = 0, marker; marker = markers[i]; i++) {
    bounds.extend(marker.getPosition());
  }

  this.map_.fitBounds(bounds);
};


/**
 *  Sets the styles.
 *
 *  @param {Object} styles The style to set.
 */
MarkerClusterer.prototype.setStyles = function(styles) {
  this.styles_ = styles;
};


/**
 *  Gets the styles.
 *
 *  @return {Object} The styles object.
 */
MarkerClusterer.prototype.getStyles = function() {
  return this.styles_;
};


/**
 * Whether zoom on click is set.
 *
 * @return {boolean} True if zoomOnClick_ is set.
 */
MarkerClusterer.prototype.isZoomOnClick = function() {
  return this.zoomOnClick_;
};

/**
 * Whether average center is set.
 *
 * @return {boolean} True if averageCenter_ is set.
 */
MarkerClusterer.prototype.isAverageCenter = function() {
  return this.averageCenter_;
};


/**
 *  Returns the array of markers in the clusterer.
 *
 *  @return {Array.<google.maps.Marker>} The markers.
 */
MarkerClusterer.prototype.getMarkers = function() {
  return this.markers_;
};


/**
 *  Returns the number of markers in the clusterer
 *
 *  @return {Number} The number of markers.
 */
MarkerClusterer.prototype.getTotalMarkers = function() {
  return this.markers_.length;
};


/**
 *  Sets the max zoom for the clusterer.
 *
 *  @param {number} maxZoom The max zoom level.
 */
MarkerClusterer.prototype.setMaxZoom = function(maxZoom) {
  this.maxZoom_ = maxZoom;
};


/**
 *  Gets the max zoom for the clusterer.
 *
 *  @return {number} The max zoom level.
 */
MarkerClusterer.prototype.getMaxZoom = function() {
  return this.maxZoom_;
};


/**
 *  The function for calculating the cluster icon image.
 *
 *  @param {Array.<google.maps.Marker>} markers The markers in the clusterer.
 *  @param {number} numStyles The number of styles available.
 *  @return {Object} A object properties: 'text' (string) and 'index' (number).
 *  @private
 */
MarkerClusterer.prototype.calculator_ = function(markers, numStyles) {
  var index = 0;
  var count = markers.length;
  var dv = count;
  while (dv !== 0) {
    dv = parseInt(dv / 10, 10);
    index++;
  }

  index = Math.min(index, numStyles);
  return {
    text: count,
    index: index
  };
};


/**
 * Set the calculator function.
 *
 * @param {function(Array, number)} calculator The function to set as the
 *     calculator. The function should return a object properties:
 *     'text' (string) and 'index' (number).
 *
 */
MarkerClusterer.prototype.setCalculator = function(calculator) {
  this.calculator_ = calculator;
};


/**
 * Get the calculator function.
 *
 * @return {function(Array, number)} the calculator function.
 */
MarkerClusterer.prototype.getCalculator = function() {
  return this.calculator_;
};


/**
 * Add an array of markers to the clusterer.
 *
 * @param {Array.<google.maps.Marker>} markers The markers to add.
 * @param {boolean=} opt_nodraw Whether to redraw the clusters.
 */
MarkerClusterer.prototype.addMarkers = function(markers, opt_nodraw) {
  for (var i = 0, marker; marker = markers[i]; i++) {
    this.pushMarkerTo_(marker);
  }
  if (!opt_nodraw) {
    this.redraw();
  }
};


/**
 * Pushes a marker to the clusterer.
 *
 * @param {google.maps.Marker} marker The marker to add.
 * @private
 */
MarkerClusterer.prototype.pushMarkerTo_ = function(marker) {
  marker.isAdded = false;
  if (marker['draggable']) {
    // If the marker is draggable add a listener so we update the clusters on
    // the drag end.
    var that = this;
    google.maps.event.addListener(marker, 'dragend', function() {
      marker.isAdded = false;
      that.repaint();
    });
  }
  this.markers_.push(marker);
};


/**
 * Adds a marker to the clusterer and redraws if needed.
 *
 * @param {google.maps.Marker} marker The marker to add.
 * @param {boolean=} opt_nodraw Whether to redraw the clusters.
 */
MarkerClusterer.prototype.addMarker = function(marker, opt_nodraw) {
  this.pushMarkerTo_(marker);
  if (!opt_nodraw) {
    this.redraw();
  }
};


/**
 * Removes a marker and returns true if removed, false if not
 *
 * @param {google.maps.Marker} marker The marker to remove
 * @return {boolean} Whether the marker was removed or not
 * @private
 */
MarkerClusterer.prototype.removeMarker_ = function(marker) {
  var index = -1;
  if (this.markers_.indexOf) {
    index = this.markers_.indexOf(marker);
  } else {
    for (var i = 0, m; m = this.markers_[i]; i++) {
      if (m == marker) {
        index = i;
        break;
      }
    }
  }

  if (index == -1) {
    // Marker is not in our list of markers.
    return false;
  }

  marker.setMap(null);

  this.markers_.splice(index, 1);

  return true;
};


/**
 * Remove a marker from the cluster.
 *
 * @param {google.maps.Marker} marker The marker to remove.
 * @param {boolean=} opt_nodraw Optional boolean to force no redraw.
 * @return {boolean} True if the marker was removed.
 */
MarkerClusterer.prototype.removeMarker = function(marker, opt_nodraw) {
  var removed = this.removeMarker_(marker);

  if (!opt_nodraw && removed) {
    this.resetViewport();
    this.redraw();
    return true;
  } else {
    return false;
  }
};


/**
 * Removes an array of markers from the cluster.
 *
 * @param {Array.<google.maps.Marker>} markers The markers to remove.
 * @param {boolean=} opt_nodraw Optional boolean to force no redraw.
 */
MarkerClusterer.prototype.removeMarkers = function(markers, opt_nodraw) {
  var removed = false;

  for (var i = 0, marker; marker = markers[i]; i++) {
    var r = this.removeMarker_(marker);
    removed = removed || r;
  }

  if (!opt_nodraw && removed) {
    this.resetViewport();
    this.redraw();
    return true;
  }
};


/**
 * Sets the clusterer's ready state.
 *
 * @param {boolean} ready The state.
 * @private
 */
MarkerClusterer.prototype.setReady_ = function(ready) {
  if (!this.ready_) {
    this.ready_ = ready;
    this.createClusters_();
  }
};


/**
 * Returns the number of clusters in the clusterer.
 *
 * @return {number} The number of clusters.
 */
MarkerClusterer.prototype.getTotalClusters = function() {
  return this.clusters_.length;
};


/**
 * Returns the google map that the clusterer is associated with.
 *
 * @return {google.maps.Map} The map.
 */
MarkerClusterer.prototype.getMap = function() {
  return this.map_;
};


/**
 * Sets the google map that the clusterer is associated with.
 *
 * @param {google.maps.Map} map The map.
 */
MarkerClusterer.prototype.setMap = function(map) {
  this.map_ = map;
};


/**
 * Returns the size of the grid.
 *
 * @return {number} The grid size.
 */
MarkerClusterer.prototype.getGridSize = function() {
  return this.gridSize_;
};


/**
 * Sets the size of the grid.
 *
 * @param {number} size The grid size.
 */
MarkerClusterer.prototype.setGridSize = function(size) {
  this.gridSize_ = size;
};


/**
 * Returns the min cluster size.
 *
 * @return {number} The grid size.
 */
MarkerClusterer.prototype.getMinClusterSize = function() {
  return this.minClusterSize_;
};

/**
 * Sets the min cluster size.
 *
 * @param {number} size The grid size.
 */
MarkerClusterer.prototype.setMinClusterSize = function(size) {
  this.minClusterSize_ = size;
};


/**
 * Extends a bounds object by the grid size.
 *
 * @param {google.maps.LatLngBounds} bounds The bounds to extend.
 * @return {google.maps.LatLngBounds} The extended bounds.
 */
MarkerClusterer.prototype.getExtendedBounds = function(bounds) {
  var projection = this.getProjection();

  // Turn the bounds into latlng.
  var tr = new google.maps.LatLng(bounds.getNorthEast().lat(),
      bounds.getNorthEast().lng());
  var bl = new google.maps.LatLng(bounds.getSouthWest().lat(),
      bounds.getSouthWest().lng());

  // Convert the points to pixels and the extend out by the grid size.
  var trPix = projection.fromLatLngToDivPixel(tr);
  trPix.x += this.gridSize_;
  trPix.y -= this.gridSize_;

  var blPix = projection.fromLatLngToDivPixel(bl);
  blPix.x -= this.gridSize_;
  blPix.y += this.gridSize_;

  // Convert the pixel points back to LatLng
  var ne = projection.fromDivPixelToLatLng(trPix);
  var sw = projection.fromDivPixelToLatLng(blPix);

  // Extend the bounds to contain the new bounds.
  bounds.extend(ne);
  bounds.extend(sw);

  return bounds;
};


/**
 * Determins if a marker is contained in a bounds.
 *
 * @param {google.maps.Marker} marker The marker to check.
 * @param {google.maps.LatLngBounds} bounds The bounds to check against.
 * @return {boolean} True if the marker is in the bounds.
 * @private
 */
MarkerClusterer.prototype.isMarkerInBounds_ = function(marker, bounds) {
  return bounds.contains(marker.getPosition());
};


/**
 * Clears all clusters and markers from the clusterer.
 */
MarkerClusterer.prototype.clearMarkers = function() {
  this.resetViewport(true);

  // Set the markers a empty array.
  this.markers_ = [];
};


/**
 * Clears all existing clusters and recreates them.
 * @param {boolean} opt_hide To also hide the marker.
 */
MarkerClusterer.prototype.resetViewport = function(opt_hide) {
  // Remove all the clusters
  for (var i = 0, cluster; cluster = this.clusters_[i]; i++) {
    cluster.remove();
  }

  // Reset the markers to not be added and to be invisible.
  for (var i = 0, marker; marker = this.markers_[i]; i++) {
    marker.isAdded = false;
    if (opt_hide) {
      marker.setMap(null);
    }
  }

  this.clusters_ = [];
};

/**
 *
 */
MarkerClusterer.prototype.repaint = function() {
  var oldClusters = this.clusters_.slice();
  this.clusters_.length = 0;
  this.resetViewport();
  this.redraw();

  // Remove the old clusters.
  // Do it in a timeout so the other clusters have been drawn first.
  window.setTimeout(function() {
    for (var i = 0, cluster; cluster = oldClusters[i]; i++) {
      cluster.remove();
    }
  }, 0);
};


/**
 * Redraws the clusters.
 */
MarkerClusterer.prototype.redraw = function() {
  this.createClusters_();
};


/**
 * Calculates the distance between two latlng locations in km.
 * @see http://www.movable-type.co.uk/scripts/latlong.html
 *
 * @param {google.maps.LatLng} p1 The first lat lng point.
 * @param {google.maps.LatLng} p2 The second lat lng point.
 * @return {number} The distance between the two points in km.
 * @private
 */
MarkerClusterer.prototype.distanceBetweenPoints_ = function(p1, p2) {
  if (!p1 || !p2) {
    return 0;
  }

  var R = 6371; // Radius of the Earth in km
  var dLat = (p2.lat() - p1.lat()) * Math.PI / 180;
  var dLon = (p2.lng() - p1.lng()) * Math.PI / 180;
  var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(p1.lat() * Math.PI / 180) * Math.cos(p2.lat() * Math.PI / 180) *
      Math.sin(dLon / 2) * Math.sin(dLon / 2);
  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  var d = R * c;
  return d;
};


/**
 * Add a marker to a cluster, or creates a new cluster.
 *
 * @param {google.maps.Marker} marker The marker to add.
 * @private
 */
MarkerClusterer.prototype.addToClosestCluster_ = function(marker) {
  var distance = 40000; // Some large number
  var clusterToAddTo = null;
  var pos = marker.getPosition();
  for (var i = 0, cluster; cluster = this.clusters_[i]; i++) {
    var center = cluster.getCenter();
    if (center) {
      var d = this.distanceBetweenPoints_(center, marker.getPosition());
      if (d < distance) {
        distance = d;
        clusterToAddTo = cluster;
      }
    }
  }

  if (clusterToAddTo && clusterToAddTo.isMarkerInClusterBounds(marker)) {
    clusterToAddTo.addMarker(marker);
  } else {
    var cluster = new Cluster(this);
    cluster.addMarker(marker);
    this.clusters_.push(cluster);
  }
};


/**
 * Creates the clusters.
 *
 * @private
 */
MarkerClusterer.prototype.createClusters_ = function() {
  if (!this.ready_) {
    return;
  }

  // Get our current map view bounds.
  // Create a new bounds object so we don't affect the map.
  var mapBounds = new google.maps.LatLngBounds(this.map_.getBounds().getSouthWest(),
      this.map_.getBounds().getNorthEast());
  var bounds = this.getExtendedBounds(mapBounds);

  for (var i = 0, marker; marker = this.markers_[i]; i++) {
    if (!marker.isAdded && this.isMarkerInBounds_(marker, bounds)) {
      this.addToClosestCluster_(marker);
    }
  }
};


/**
 * A cluster that contains markers.
 *
 * @param {MarkerClusterer} markerClusterer The markerclusterer that this
 *     cluster is associated with.
 * @constructor
 * @ignore
 */
function Cluster(markerClusterer) {
  this.markerClusterer_ = markerClusterer;
  this.map_ = markerClusterer.getMap();
  this.gridSize_ = markerClusterer.getGridSize();
  this.minClusterSize_ = markerClusterer.getMinClusterSize();
  this.averageCenter_ = markerClusterer.isAverageCenter();
  this.center_ = null;
  this.markers_ = [];
  this.bounds_ = null;
  this.clusterIcon_ = new ClusterIcon(this, markerClusterer.getStyles(),
      markerClusterer.getGridSize());
}

/**
 * Determins if a marker is already added to the cluster.
 *
 * @param {google.maps.Marker} marker The marker to check.
 * @return {boolean} True if the marker is already added.
 */
Cluster.prototype.isMarkerAlreadyAdded = function(marker) {
  if (this.markers_.indexOf) {
    return this.markers_.indexOf(marker) != -1;
  } else {
    for (var i = 0, m; m = this.markers_[i]; i++) {
      if (m == marker) {
        return true;
      }
    }
  }
  return false;
};


/**
 * Add a marker the cluster.
 *
 * @param {google.maps.Marker} marker The marker to add.
 * @return {boolean} True if the marker was added.
 */
Cluster.prototype.addMarker = function(marker) {
  if (this.isMarkerAlreadyAdded(marker)) {
    return false;
  }

  if (!this.center_) {
    this.center_ = marker.getPosition();
    this.calculateBounds_();
  } else {
    if (this.averageCenter_) {
      var l = this.markers_.length + 1;
      var lat = (this.center_.lat() * (l-1) + marker.getPosition().lat()) / l;
      var lng = (this.center_.lng() * (l-1) + marker.getPosition().lng()) / l;
      this.center_ = new google.maps.LatLng(lat, lng);
      this.calculateBounds_();
    }
  }

  marker.isAdded = true;
  this.markers_.push(marker);

  var len = this.markers_.length;
  if (len < this.minClusterSize_ && marker.getMap() != this.map_) {
    // Min cluster size not reached so show the marker.
    marker.setMap(this.map_);
  }

  if (len == this.minClusterSize_) {
    // Hide the markers that were showing.
    for (var i = 0; i < len; i++) {
      this.markers_[i].setMap(null);
    }
  }

  if (len >= this.minClusterSize_) {
    marker.setMap(null);
  }

  this.updateIcon();
  return true;
};


/**
 * Returns the marker clusterer that the cluster is associated with.
 *
 * @return {MarkerClusterer} The associated marker clusterer.
 */
Cluster.prototype.getMarkerClusterer = function() {
  return this.markerClusterer_;
};


/**
 * Returns the bounds of the cluster.
 *
 * @return {google.maps.LatLngBounds} the cluster bounds.
 */
Cluster.prototype.getBounds = function() {
  var bounds = new google.maps.LatLngBounds(this.center_, this.center_);
  var markers = this.getMarkers();
  for (var i = 0, marker; marker = markers[i]; i++) {
    bounds.extend(marker.getPosition());
  }
  return bounds;
};


/**
 * Removes the cluster
 */
Cluster.prototype.remove = function() {
  this.clusterIcon_.remove();
  this.markers_.length = 0;
  delete this.markers_;
};


/**
 * Returns the center of the cluster.
 *
 * @return {number} The cluster center.
 */
Cluster.prototype.getSize = function() {
  return this.markers_.length;
};


/**
 * Returns the center of the cluster.
 *
 * @return {Array.<google.maps.Marker>} The cluster center.
 */
Cluster.prototype.getMarkers = function() {
  return this.markers_;
};


/**
 * Returns the center of the cluster.
 *
 * @return {google.maps.LatLng} The cluster center.
 */
Cluster.prototype.getCenter = function() {
  return this.center_;
};


/**
 * Calculated the extended bounds of the cluster with the grid.
 *
 * @private
 */
Cluster.prototype.calculateBounds_ = function() {
  var bounds = new google.maps.LatLngBounds(this.center_, this.center_);
  this.bounds_ = this.markerClusterer_.getExtendedBounds(bounds);
};


/**
 * Determines if a marker lies in the clusters bounds.
 *
 * @param {google.maps.Marker} marker The marker to check.
 * @return {boolean} True if the marker lies in the bounds.
 */
Cluster.prototype.isMarkerInClusterBounds = function(marker) {
  return this.bounds_.contains(marker.getPosition());
};


/**
 * Returns the map that the cluster is associated with.
 *
 * @return {google.maps.Map} The map.
 */
Cluster.prototype.getMap = function() {
  return this.map_;
};


/**
 * Updates the cluster icon
 */
Cluster.prototype.updateIcon = function() {
  var zoom = this.map_.getZoom();
  var mz = this.markerClusterer_.getMaxZoom();

  if (mz && zoom > mz) {
    // The zoom is greater than our max zoom so show all the markers in cluster.
    for (var i = 0, marker; marker = this.markers_[i]; i++) {
      marker.setMap(this.map_);
    }
    return;
  }

  if (this.markers_.length < this.minClusterSize_) {
    // Min cluster size not yet reached.
    this.clusterIcon_.hide();
    return;
  }

  var numStyles = this.markerClusterer_.getStyles().length;
  var sums = this.markerClusterer_.getCalculator()(this.markers_, numStyles);
  this.clusterIcon_.setCenter(this.center_);
  this.clusterIcon_.setSums(sums);
  this.clusterIcon_.show();
};


/**
 * A cluster icon
 *
 * @param {Cluster} cluster The cluster to be associated with.
 * @param {Object} styles An object that has style properties:
 *     'url': (string) The image url.
 *     'height': (number) The image height.
 *     'width': (number) The image width.
 *     'anchor': (Array) The anchor position of the label text.
 *     'textColor': (string) The text color.
 *     'textSize': (number) The text size.
 *     'backgroundPosition: (string) The background postition x, y.
 * @param {number=} opt_padding Optional padding to apply to the cluster icon.
 * @constructor
 * @extends google.maps.OverlayView
 * @ignore
 */
function ClusterIcon(cluster, styles, opt_padding) {
  cluster.getMarkerClusterer().extend(ClusterIcon, google.maps.OverlayView);

  this.styles_ = styles;
  this.padding_ = opt_padding || 0;
  this.cluster_ = cluster;
  this.center_ = null;
  this.map_ = cluster.getMap();
  this.div_ = null;
  this.sums_ = null;
  this.visible_ = false;

  this.setMap(this.map_);
}


/**
 * Triggers the clusterclick event and zoom's if the option is set.
 *
 * @param {google.maps.MouseEvent} event The event to propagate
 */
ClusterIcon.prototype.triggerClusterClick = function(event) {
  var markerClusterer = this.cluster_.getMarkerClusterer();

  // Trigger the clusterclick event.
  google.maps.event.trigger(markerClusterer, 'clusterclick', this.cluster_, event);

  if (markerClusterer.isZoomOnClick()) {
    // Zoom into the cluster.
    this.map_.fitBounds(this.cluster_.getBounds());
  }
};


/**
 * Adding the cluster icon to the dom.
 * @ignore
 */
ClusterIcon.prototype.onAdd = function() {
  this.div_ = document.createElement('DIV');
  if (this.visible_) {
    var pos = this.getPosFromLatLng_(this.center_);
    this.div_.style.cssText = this.createCss(pos);
    this.div_.innerHTML = this.sums_.text;
  }

  var panes = this.getPanes();
  panes.overlayMouseTarget.appendChild(this.div_);

  var that = this;
  var isDragging = false;
  google.maps.event.addDomListener(this.div_, 'click', function(event) {
    // Only perform click when not preceded by a drag
    if (!isDragging) {
      that.triggerClusterClick(event);
    }
  });
  google.maps.event.addDomListener(this.div_, 'mousedown', function() {
    isDragging = false;
  });
  google.maps.event.addDomListener(this.div_, 'mousemove', function() {
    isDragging = true;
  });
};


/**
 * Returns the position to place the div dending on the latlng.
 *
 * @param {google.maps.LatLng} latlng The position in latlng.
 * @return {google.maps.Point} The position in pixels.
 * @private
 */
ClusterIcon.prototype.getPosFromLatLng_ = function(latlng) {
  var pos = this.getProjection().fromLatLngToDivPixel(latlng);

  if (typeof this.iconAnchor_ === 'object' && this.iconAnchor_.length === 2) {
    pos.x -= this.iconAnchor_[0];
    pos.y -= this.iconAnchor_[1];
  } else {
    pos.x -= parseInt(this.width_ / 2, 10);
    pos.y -= parseInt(this.height_ / 2, 10);
  }
  return pos;
};


/**
 * Draw the icon.
 * @ignore
 */
ClusterIcon.prototype.draw = function() {
  if (this.visible_) {
    var pos = this.getPosFromLatLng_(this.center_);
    this.div_.style.top = pos.y + 'px';
    this.div_.style.left = pos.x + 'px';
  }
};


/**
 * Hide the icon.
 */
ClusterIcon.prototype.hide = function() {
  if (this.div_) {
    this.div_.style.display = 'none';
  }
  this.visible_ = false;
};


/**
 * Position and show the icon.
 */
ClusterIcon.prototype.show = function() {
  if (this.div_) {
    var pos = this.getPosFromLatLng_(this.center_);
    this.div_.style.cssText = this.createCss(pos);
    this.div_.style.display = '';
  }
  this.visible_ = true;
};


/**
 * Remove the icon from the map
 */
ClusterIcon.prototype.remove = function() {
  this.setMap(null);
};


/**
 * Implementation of the onRemove interface.
 * @ignore
 */
ClusterIcon.prototype.onRemove = function() {
  if (this.div_ && this.div_.parentNode) {
    this.hide();
    this.div_.parentNode.removeChild(this.div_);
    this.div_ = null;
  }
};


/**
 * Set the sums of the icon.
 *
 * @param {Object} sums The sums containing:
 *   'text': (string) The text to display in the icon.
 *   'index': (number) The style index of the icon.
 */
ClusterIcon.prototype.setSums = function(sums) {
  this.sums_ = sums;
  this.text_ = sums.text;
  this.index_ = sums.index;
  if (this.div_) {
    this.div_.innerHTML = sums.text;
  }

  this.useStyle();
};


/**
 * Sets the icon to the the styles.
 */
ClusterIcon.prototype.useStyle = function() {
  var index = Math.max(0, this.sums_.index - 1);
  index = Math.min(this.styles_.length - 1, index);
  var style = this.styles_[index];
  this.url_ = style['url'];
  this.height_ = style['height'];
  this.width_ = style['width'];
  this.textColor_ = style['textColor'];
  this.anchor_ = style['anchor'];
  this.textSize_ = style['textSize'];
  this.backgroundPosition_ = style['backgroundPosition'];
  this.iconAnchor_ = style['iconAnchor'];
};


/**
 * Sets the center of the icon.
 *
 * @param {google.maps.LatLng} center The latlng to set as the center.
 */
ClusterIcon.prototype.setCenter = function(center) {
  this.center_ = center;
};


/**
 * Create the css text based on the position of the icon.
 *
 * @param {google.maps.Point} pos The position.
 * @return {string} The css style text.
 */
ClusterIcon.prototype.createCss = function(pos) {
  var style = [];
  style.push('background-image:url(' + this.url_ + ');');
  var backgroundPosition = this.backgroundPosition_ ? this.backgroundPosition_ : '0 0';
  style.push('background-position:' + backgroundPosition + ';');

  if (typeof this.anchor_ === 'object') {
    if (typeof this.anchor_[0] === 'number' && this.anchor_[0] > 0 &&
        this.anchor_[0] < this.height_) {
      style.push('height:' + (this.height_ - this.anchor_[0]) +
          'px; padding-top:' + this.anchor_[0] + 'px;');
    } else if (typeof this.anchor_[0] === 'number' && this.anchor_[0] < 0 &&
        -this.anchor_[0] < this.height_) {
      style.push('height:' + this.height_ + 'px; line-height:' + (this.height_ + this.anchor_[0]) +
          'px;');
    } else {
      style.push('height:' + this.height_ + 'px; line-height:' + this.height_ +
          'px;');
    }
    if (typeof this.anchor_[1] === 'number' && this.anchor_[1] > 0 &&
        this.anchor_[1] < this.width_) {
      style.push('width:' + (this.width_ - this.anchor_[1]) +
          'px; padding-left:' + this.anchor_[1] + 'px;');
    } else {
      style.push('width:' + this.width_ + 'px; text-align:center;');
    }
  } else {
    style.push('height:' + this.height_ + 'px; line-height:' +
        this.height_ + 'px; width:' + this.width_ + 'px; text-align:center;');
  }

  var txtColor = this.textColor_ ? this.textColor_ : 'black';
  var txtSize = this.textSize_ ? this.textSize_ : 11;

  style.push('cursor:pointer; top:' + pos.y + 'px; left:' +
      pos.x + 'px; color:' + txtColor + '; position:absolute; font-size:' +
      txtSize + 'px; font-family:Arial,sans-serif; font-weight:bold');
  return style.join('');
};


// Export Symbols for Closure
// If you are not going to compile with closure then you can remove the
// code below.
window['MarkerClusterer'] = MarkerClusterer;
MarkerClusterer.prototype['addMarker'] = MarkerClusterer.prototype.addMarker;
MarkerClusterer.prototype['addMarkers'] = MarkerClusterer.prototype.addMarkers;
MarkerClusterer.prototype['clearMarkers'] =
    MarkerClusterer.prototype.clearMarkers;
MarkerClusterer.prototype['fitMapToMarkers'] =
    MarkerClusterer.prototype.fitMapToMarkers;
MarkerClusterer.prototype['getCalculator'] =
    MarkerClusterer.prototype.getCalculator;
MarkerClusterer.prototype['getGridSize'] =
    MarkerClusterer.prototype.getGridSize;
MarkerClusterer.prototype['getExtendedBounds'] =
    MarkerClusterer.prototype.getExtendedBounds;
MarkerClusterer.prototype['getMap'] = MarkerClusterer.prototype.getMap;
MarkerClusterer.prototype['getMarkers'] = MarkerClusterer.prototype.getMarkers;
MarkerClusterer.prototype['getMaxZoom'] = MarkerClusterer.prototype.getMaxZoom;
MarkerClusterer.prototype['getStyles'] = MarkerClusterer.prototype.getStyles;
MarkerClusterer.prototype['getTotalClusters'] =
    MarkerClusterer.prototype.getTotalClusters;
MarkerClusterer.prototype['getTotalMarkers'] =
    MarkerClusterer.prototype.getTotalMarkers;
MarkerClusterer.prototype['redraw'] = MarkerClusterer.prototype.redraw;
MarkerClusterer.prototype['removeMarker'] =
    MarkerClusterer.prototype.removeMarker;
MarkerClusterer.prototype['removeMarkers'] =
    MarkerClusterer.prototype.removeMarkers;
MarkerClusterer.prototype['resetViewport'] =
    MarkerClusterer.prototype.resetViewport;
MarkerClusterer.prototype['repaint'] =
    MarkerClusterer.prototype.repaint;
MarkerClusterer.prototype['setCalculator'] =
    MarkerClusterer.prototype.setCalculator;
MarkerClusterer.prototype['setGridSize'] =
    MarkerClusterer.prototype.setGridSize;
MarkerClusterer.prototype['setMaxZoom'] =
    MarkerClusterer.prototype.setMaxZoom;
MarkerClusterer.prototype['onAdd'] = MarkerClusterer.prototype.onAdd;
MarkerClusterer.prototype['draw'] = MarkerClusterer.prototype.draw;

Cluster.prototype['getCenter'] = Cluster.prototype.getCenter;
Cluster.prototype['getSize'] = Cluster.prototype.getSize;
Cluster.prototype['getMarkers'] = Cluster.prototype.getMarkers;

ClusterIcon.prototype['onAdd'] = ClusterIcon.prototype.onAdd;
ClusterIcon.prototype['draw'] = ClusterIcon.prototype.draw;
ClusterIcon.prototype['onRemove'] = ClusterIcon.prototype.onRemove;
/**
 * @name InfoBox
 * @version 1.1.13 [March 19, 2014]
 * @author Gary Little (inspired by proof-of-concept code from Pamela Fox of Google)
 * @copyright Copyright 2010 Gary Little [gary at luxcentral.com]
 * @fileoverview InfoBox extends the Google Maps JavaScript API V3 <tt>OverlayView</tt> class.
 *  <p>
 *  An InfoBox behaves like a <tt>google.maps.InfoWindow</tt>, but it supports several
 *  additional properties for advanced styling. An InfoBox can also be used as a map label.
 *  <p>
 *  An InfoBox also fires the same events as a <tt>google.maps.InfoWindow</tt>.
 */

/*!
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*jslint browser:true */
/*global google */

/**
 * @name InfoBoxOptions
 * @class This class represents the optional parameter passed to the {@link InfoBox} constructor.
 * @property {string|Node} content The content of the InfoBox (plain text or an HTML DOM node).
 * @property {boolean} [disableAutoPan=false] Disable auto-pan on <tt>open</tt>.
 * @property {number} maxWidth The maximum width (in pixels) of the InfoBox. Set to 0 if no maximum.
 * @property {Size} pixelOffset The offset (in pixels) from the top left corner of the InfoBox
 *  (or the bottom left corner if the <code>alignBottom</code> property is <code>true</code>)
 *  to the map pixel corresponding to <tt>position</tt>.
 * @property {LatLng} position The geographic location at which to display the InfoBox.
 * @property {number} zIndex The CSS z-index style value for the InfoBox.
 *  Note: This value overrides a zIndex setting specified in the <tt>boxStyle</tt> property.
 * @property {string} [boxClass="infoBox"] The name of the CSS class defining the styles for the InfoBox container.
 * @property {Object} [boxStyle] An object literal whose properties define specific CSS
 *  style values to be applied to the InfoBox. Style values defined here override those that may
 *  be defined in the <code>boxClass</code> style sheet. If this property is changed after the
 *  InfoBox has been created, all previously set styles (except those defined in the style sheet)
 *  are removed from the InfoBox before the new style values are applied.
 * @property {string} closeBoxMargin The CSS margin style value for the close box.
 *  The default is "2px" (a 2-pixel margin on all sides).
 * @property {string} closeBoxURL The URL of the image representing the close box.
 *  Note: The default is the URL for Google's standard close box.
 *  Set this property to "" if no close box is required.
 * @property {Size} infoBoxClearance Minimum offset (in pixels) from the InfoBox to the
 *  map edge after an auto-pan.
 * @property {boolean} [isHidden=false] Hide the InfoBox on <tt>open</tt>.
 *  [Deprecated in favor of the <tt>visible</tt> property.]
 * @property {boolean} [visible=true] Show the InfoBox on <tt>open</tt>.
 * @property {boolean} alignBottom Align the bottom left corner of the InfoBox to the <code>position</code>
 *  location (default is <tt>false</tt> which means that the top left corner of the InfoBox is aligned).
 * @property {string} pane The pane where the InfoBox is to appear (default is "floatPane").
 *  Set the pane to "mapPane" if the InfoBox is being used as a map label.
 *  Valid pane names are the property names for the <tt>google.maps.MapPanes</tt> object.
 * @property {boolean} enableEventPropagation Propagate mousedown, mousemove, mouseover, mouseout,
 *  mouseup, click, dblclick, touchstart, touchend, touchmove, and contextmenu events in the InfoBox
 *  (default is <tt>false</tt> to mimic the behavior of a <tt>google.maps.InfoWindow</tt>). Set
 *  this property to <tt>true</tt> if the InfoBox is being used as a map label.
 */

/**
 * Creates an InfoBox with the options specified in {@link InfoBoxOptions}.
 *  Call <tt>InfoBox.open</tt> to add the box to the map.
 * @constructor
 * @param {InfoBoxOptions} [opt_opts]
 */
function InfoBox(opt_opts) {

    opt_opts = opt_opts || {};

    google.maps.OverlayView.apply(this, arguments);

    // Standard options (in common with google.maps.InfoWindow):
    //
    this.content_ = opt_opts.content || "";
    this.disableAutoPan_ = opt_opts.disableAutoPan || false;
    this.maxWidth_ = opt_opts.maxWidth || 0;
    this.pixelOffset_ = opt_opts.pixelOffset || new google.maps.Size(0, 0);
    this.position_ = opt_opts.position || new google.maps.LatLng(0, 0);
    this.zIndex_ = opt_opts.zIndex || null;

    // Additional options (unique to InfoBox):
    //
    this.boxClass_ = opt_opts.boxClass || "infoBox";
    this.boxStyle_ = opt_opts.boxStyle || {};
    this.closeBoxMargin_ = opt_opts.closeBoxMargin || "2px";
    this.closeBoxURL_ = opt_opts.closeBoxURL || "http://www.google.com/intl/en_us/mapfiles/close.gif";
    if (opt_opts.closeBoxURL === "") {
        this.closeBoxURL_ = "";
    }
    this.infoBoxClearance_ = opt_opts.infoBoxClearance || new google.maps.Size(1, 1);

    if (typeof opt_opts.visible === "undefined") {
        if (typeof opt_opts.isHidden === "undefined") {
            opt_opts.visible = true;
        } else {
            opt_opts.visible = !opt_opts.isHidden;
        }
    }
    this.isHidden_ = !opt_opts.visible;

    this.alignBottom_ = opt_opts.alignBottom || false;
    this.pane_ = opt_opts.pane || "floatPane";
    this.enableEventPropagation_ = opt_opts.enableEventPropagation || false;

    this.div_ = null;
    this.closeListener_ = null;
    this.moveListener_ = null;
    this.contextListener_ = null;
    this.eventListeners_ = null;
    this.fixedWidthSet_ = null;
}

/* InfoBox extends OverlayView in the Google Maps API v3.
 */
InfoBox.prototype = new google.maps.OverlayView();

/**
 * Creates the DIV representing the InfoBox.
 * @private
 */
InfoBox.prototype.createInfoBoxDiv_ = function () {

    var i;
    var events;
    var bw;
    var me = this;

    // This handler prevents an event in the InfoBox from being passed on to the map.
    //
    var cancelHandler = function (e) {
        e.cancelBubble = true;
        if (e.stopPropagation) {
            e.stopPropagation();
        }
    };

    // This handler ignores the current event in the InfoBox and conditionally prevents
    // the event from being passed on to the map. It is used for the contextmenu event.
    //
    var ignoreHandler = function (e) {

        e.returnValue = false;

        if (e.preventDefault) {

            e.preventDefault();
        }

        if (!me.enableEventPropagation_) {

            cancelHandler(e);
        }
    };

    if (!this.div_) {

        this.div_ = document.createElement("div");

        this.setBoxStyle_();

        if (typeof this.content_.nodeType === "undefined") {
            this.div_.innerHTML = this.getCloseBoxImg_() + this.content_;
        } else {
            this.div_.innerHTML = this.getCloseBoxImg_();
            this.div_.appendChild(this.content_);
        }

        // Add the InfoBox DIV to the DOM
        this.getPanes()[this.pane_].appendChild(this.div_);

        this.addClickHandler_();

        if (this.div_.style.width) {

            this.fixedWidthSet_ = true;

        } else {

            if (this.maxWidth_ !== 0 && this.div_.offsetWidth > this.maxWidth_) {

                this.div_.style.width = this.maxWidth_;
                this.div_.style.overflow = "auto";
                this.fixedWidthSet_ = true;

            } else { // The following code is needed to overcome problems with MSIE

                bw = this.getBoxWidths_();

                this.div_.style.width = (this.div_.offsetWidth - bw.left - bw.right) + "px";
                this.fixedWidthSet_ = false;
            }
        }

        this.panBox_(this.disableAutoPan_);

        if (!this.enableEventPropagation_) {

            this.eventListeners_ = [];

            // Cancel event propagation.
            //
            // Note: mousemove not included (to resolve Issue 152)
            events = ["mousedown", "mouseover", "mouseout", "mouseup",
                "click", "dblclick", "touchstart", "touchend", "touchmove"];

            for (i = 0; i < events.length; i++) {

                this.eventListeners_.push(google.maps.event.addDomListener(this.div_, events[i], cancelHandler));
            }

            // Workaround for Google bug that causes the cursor to change to a pointer
            // when the mouse moves over a marker underneath InfoBox.
            this.eventListeners_.push(google.maps.event.addDomListener(this.div_, "mouseover", function (e) {
                this.style.cursor = "default";
            }));
        }

        this.contextListener_ = google.maps.event.addDomListener(this.div_, "contextmenu", ignoreHandler);

        /**
         * This event is fired when the DIV containing the InfoBox's content is attached to the DOM.
         * @name InfoBox#domready
         * @event
         */
        google.maps.event.trigger(this, "domready");
    }
};

/**
 * Returns the HTML <IMG> tag for the close box.
 * @private
 */
InfoBox.prototype.getCloseBoxImg_ = function () {

    var img = "";

    if (this.closeBoxURL_ !== "") {

        img  = "<img";
        img += " src='" + this.closeBoxURL_ + "'";
        img += " align=right"; // Do this because Opera chokes on style='float: right;'
        img += " style='";
        img += " position: relative;"; // Required by MSIE
        img += " cursor: pointer;";
        img += " margin: " + this.closeBoxMargin_ + ";";
        img += "'>";
    }

    return img;
};

/**
 * Adds the click handler to the InfoBox close box.
 * @private
 */
InfoBox.prototype.addClickHandler_ = function () {

    var closeBox;

    if (this.closeBoxURL_ !== "") {

        closeBox = this.div_.firstChild;
        this.closeListener_ = google.maps.event.addDomListener(closeBox, "click", this.getCloseClickHandler_());

    } else {

        this.closeListener_ = null;
    }
};

/**
 * Returns the function to call when the user clicks the close box of an InfoBox.
 * @private
 */
InfoBox.prototype.getCloseClickHandler_ = function () {

    var me = this;

    return function (e) {

        // 1.0.3 fix: Always prevent propagation of a close box click to the map:
        e.cancelBubble = true;

        if (e.stopPropagation) {

            e.stopPropagation();
        }

        /**
         * This event is fired when the InfoBox's close box is clicked.
         * @name InfoBox#closeclick
         * @event
         */
        google.maps.event.trigger(me, "closeclick");

        me.close();
    };
};

/**
 * Pans the map so that the InfoBox appears entirely within the map's visible area.
 * @private
 */
InfoBox.prototype.panBox_ = function (disablePan) {

    var map;
    var bounds;
    var xOffset = 0, yOffset = 0;

    if (!disablePan) {

        map = this.getMap();

        if (map instanceof google.maps.Map) { // Only pan if attached to map, not panorama

            if (!map.getBounds().contains(this.position_)) {
                // Marker not in visible area of map, so set center
                // of map to the marker position first.
                map.setCenter(this.position_);
            }

            bounds = map.getBounds();

            var mapDiv = map.getDiv();
            var mapWidth = mapDiv.offsetWidth;
            var mapHeight = mapDiv.offsetHeight;
            var iwOffsetX = this.pixelOffset_.width;
            var iwOffsetY = this.pixelOffset_.height;
            var iwWidth = this.div_.offsetWidth;
            var iwHeight = this.div_.offsetHeight;
            var padX = this.infoBoxClearance_.width;
            var padY = this.infoBoxClearance_.height;
            var pixPosition = this.getProjection().fromLatLngToContainerPixel(this.position_);

            if (pixPosition.x < (-iwOffsetX + padX)) {
                xOffset = pixPosition.x + iwOffsetX - padX;
            } else if ((pixPosition.x + iwWidth + iwOffsetX + padX) > mapWidth) {
                xOffset = pixPosition.x + iwWidth + iwOffsetX + padX - mapWidth;
            }
            if (this.alignBottom_) {
                if (pixPosition.y < (-iwOffsetY + padY + iwHeight)) {
                    yOffset = pixPosition.y + iwOffsetY - padY - iwHeight;
                } else if ((pixPosition.y + iwOffsetY + padY) > mapHeight) {
                    yOffset = pixPosition.y + iwOffsetY + padY - mapHeight;
                }
            } else {
                if (pixPosition.y < (-iwOffsetY + padY)) {
                    yOffset = pixPosition.y + iwOffsetY - padY;
                } else if ((pixPosition.y + iwHeight + iwOffsetY + padY) > mapHeight) {
                    yOffset = pixPosition.y + iwHeight + iwOffsetY + padY - mapHeight;
                }
            }

            if (!(xOffset === 0 && yOffset === 0)) {

                // Move the map to the shifted center.
                //
                var c = map.getCenter();
                map.panBy(xOffset, yOffset);
            }
        }
    }
};

/**
 * Sets the style of the InfoBox by setting the style sheet and applying
 * other specific styles requested.
 * @private
 */
InfoBox.prototype.setBoxStyle_ = function () {

    var i, boxStyle;

    if (this.div_) {

        // Apply style values from the style sheet defined in the boxClass parameter:
        this.div_.className = this.boxClass_;

        // Clear existing inline style values:
        this.div_.style.cssText = "";

        // Apply style values defined in the boxStyle parameter:
        boxStyle = this.boxStyle_;
        for (i in boxStyle) {

            if (boxStyle.hasOwnProperty(i)) {

                this.div_.style[i] = boxStyle[i];
            }
        }

        // Fix for iOS disappearing InfoBox problem.
        // See http://stackoverflow.com/questions/9229535/google-maps-markers-disappear-at-certain-zoom-level-only-on-iphone-ipad
        this.div_.style.WebkitTransform = "translateZ(0)";

        // Fix up opacity style for benefit of MSIE:
        //
        if (typeof this.div_.style.opacity !== "undefined" && this.div_.style.opacity !== "") {
            // See http://www.quirksmode.org/css/opacity.html
            this.div_.style.MsFilter = "\"progid:DXImageTransform.Microsoft.Alpha(Opacity=" + (this.div_.style.opacity * 100) + ")\"";
            this.div_.style.filter = "alpha(opacity=" + (this.div_.style.opacity * 100) + ")";
        }

        // Apply required styles:
        //
        this.div_.style.position = "absolute";
        this.div_.style.visibility = 'hidden';
        if (this.zIndex_ !== null) {

            this.div_.style.zIndex = this.zIndex_;
        }
    }
};

/**
 * Get the widths of the borders of the InfoBox.
 * @private
 * @return {Object} widths object (top, bottom left, right)
 */
InfoBox.prototype.getBoxWidths_ = function () {

    var computedStyle;
    var bw = {top: 0, bottom: 0, left: 0, right: 0};
    var box = this.div_;

    if (document.defaultView && document.defaultView.getComputedStyle) {

        computedStyle = box.ownerDocument.defaultView.getComputedStyle(box, "");

        if (computedStyle) {

            // The computed styles are always in pixel units (good!)
            bw.top = parseInt(computedStyle.borderTopWidth, 10) || 0;
            bw.bottom = parseInt(computedStyle.borderBottomWidth, 10) || 0;
            bw.left = parseInt(computedStyle.borderLeftWidth, 10) || 0;
            bw.right = parseInt(computedStyle.borderRightWidth, 10) || 0;
        }

    } else if (document.documentElement.currentStyle) { // MSIE

        if (box.currentStyle) {

            // The current styles may not be in pixel units, but assume they are (bad!)
            bw.top = parseInt(box.currentStyle.borderTopWidth, 10) || 0;
            bw.bottom = parseInt(box.currentStyle.borderBottomWidth, 10) || 0;
            bw.left = parseInt(box.currentStyle.borderLeftWidth, 10) || 0;
            bw.right = parseInt(box.currentStyle.borderRightWidth, 10) || 0;
        }
    }

    return bw;
};

/**
 * Invoked when <tt>close</tt> is called. Do not call it directly.
 */
InfoBox.prototype.onRemove = function () {

    if (this.div_) {

        this.div_.parentNode.removeChild(this.div_);
        this.div_ = null;
    }
};

/**
 * Draws the InfoBox based on the current map projection and zoom level.
 */
InfoBox.prototype.draw = function () {

    this.createInfoBoxDiv_();

    var pixPosition = this.getProjection().fromLatLngToDivPixel(this.position_);

    this.div_.style.left = (pixPosition.x + this.pixelOffset_.width) + "px";

    if (this.alignBottom_) {
        this.div_.style.bottom = -(pixPosition.y + this.pixelOffset_.height) + "px";
    } else {
        this.div_.style.top = (pixPosition.y + this.pixelOffset_.height) + "px";
    }

    if (this.isHidden_) {

        this.div_.style.visibility = "hidden";

    } else {

        this.div_.style.visibility = "visible";
    }
};

/**
 * Sets the options for the InfoBox. Note that changes to the <tt>maxWidth</tt>,
 *  <tt>closeBoxMargin</tt>, <tt>closeBoxURL</tt>, and <tt>enableEventPropagation</tt>
 *  properties have no affect until the current InfoBox is <tt>close</tt>d and a new one
 *  is <tt>open</tt>ed.
 * @param {InfoBoxOptions} opt_opts
 */
InfoBox.prototype.setOptions = function (opt_opts) {
    if (typeof opt_opts.boxClass !== "undefined") { // Must be first

        this.boxClass_ = opt_opts.boxClass;
        this.setBoxStyle_();
    }
    if (typeof opt_opts.boxStyle !== "undefined") { // Must be second

        this.boxStyle_ = opt_opts.boxStyle;
        this.setBoxStyle_();
    }
    if (typeof opt_opts.content !== "undefined") {

        this.setContent(opt_opts.content);
    }
    if (typeof opt_opts.disableAutoPan !== "undefined") {

        this.disableAutoPan_ = opt_opts.disableAutoPan;
    }
    if (typeof opt_opts.maxWidth !== "undefined") {

        this.maxWidth_ = opt_opts.maxWidth;
    }
    if (typeof opt_opts.pixelOffset !== "undefined") {

        this.pixelOffset_ = opt_opts.pixelOffset;
    }
    if (typeof opt_opts.alignBottom !== "undefined") {

        this.alignBottom_ = opt_opts.alignBottom;
    }
    if (typeof opt_opts.position !== "undefined") {

        this.setPosition(opt_opts.position);
    }
    if (typeof opt_opts.zIndex !== "undefined") {

        this.setZIndex(opt_opts.zIndex);
    }
    if (typeof opt_opts.closeBoxMargin !== "undefined") {

        this.closeBoxMargin_ = opt_opts.closeBoxMargin;
    }
    if (typeof opt_opts.closeBoxURL !== "undefined") {

        this.closeBoxURL_ = opt_opts.closeBoxURL;
    }
    if (typeof opt_opts.infoBoxClearance !== "undefined") {

        this.infoBoxClearance_ = opt_opts.infoBoxClearance;
    }
    if (typeof opt_opts.isHidden !== "undefined") {

        this.isHidden_ = opt_opts.isHidden;
    }
    if (typeof opt_opts.visible !== "undefined") {

        this.isHidden_ = !opt_opts.visible;
    }
    if (typeof opt_opts.enableEventPropagation !== "undefined") {

        this.enableEventPropagation_ = opt_opts.enableEventPropagation;
    }

    if (this.div_) {

        this.draw();
    }
};

/**
 * Sets the content of the InfoBox.
 *  The content can be plain text or an HTML DOM node.
 * @param {string|Node} content
 */
InfoBox.prototype.setContent = function (content) {
    this.content_ = content;

    if (this.div_) {

        if (this.closeListener_) {

            google.maps.event.removeListener(this.closeListener_);
            this.closeListener_ = null;
        }

        // Odd code required to make things work with MSIE.
        //
        if (!this.fixedWidthSet_) {

            this.div_.style.width = "";
        }

        if (typeof content.nodeType === "undefined") {
            this.div_.innerHTML = this.getCloseBoxImg_() + content;
        } else {
            this.div_.innerHTML = this.getCloseBoxImg_();
            this.div_.appendChild(content);
        }

        // Perverse code required to make things work with MSIE.
        // (Ensures the close box does, in fact, float to the right.)
        //
        if (!this.fixedWidthSet_) {
            this.div_.style.width = this.div_.offsetWidth + "px";
            if (typeof content.nodeType === "undefined") {
                this.div_.innerHTML = this.getCloseBoxImg_() + content;
            } else {
                this.div_.innerHTML = this.getCloseBoxImg_();
                this.div_.appendChild(content);
            }
        }

        this.addClickHandler_();
    }

    /**
     * This event is fired when the content of the InfoBox changes.
     * @name InfoBox#content_changed
     * @event
     */
    google.maps.event.trigger(this, "content_changed");
};

/**
 * Sets the geographic location of the InfoBox.
 * @param {LatLng} latlng
 */
InfoBox.prototype.setPosition = function (latlng) {

    this.position_ = latlng;

    if (this.div_) {

        this.draw();
    }

    /**
     * This event is fired when the position of the InfoBox changes.
     * @name InfoBox#position_changed
     * @event
     */
    google.maps.event.trigger(this, "position_changed");
};

/**
 * Sets the zIndex style for the InfoBox.
 * @param {number} index
 */
InfoBox.prototype.setZIndex = function (index) {

    this.zIndex_ = index;

    if (this.div_) {

        this.div_.style.zIndex = index;
    }

    /**
     * This event is fired when the zIndex of the InfoBox changes.
     * @name InfoBox#zindex_changed
     * @event
     */
    google.maps.event.trigger(this, "zindex_changed");
};

/**
 * Sets the visibility of the InfoBox.
 * @param {boolean} isVisible
 */
InfoBox.prototype.setVisible = function (isVisible) {

    this.isHidden_ = !isVisible;
    if (this.div_) {
        this.div_.style.visibility = (this.isHidden_ ? "hidden" : "visible");
    }
};

/**
 * Returns the content of the InfoBox.
 * @returns {string}
 */
InfoBox.prototype.getContent = function () {

    return this.content_;
};

/**
 * Returns the geographic location of the InfoBox.
 * @returns {LatLng}
 */
InfoBox.prototype.getPosition = function () {

    return this.position_;
};

/**
 * Returns the zIndex for the InfoBox.
 * @returns {number}
 */
InfoBox.prototype.getZIndex = function () {

    return this.zIndex_;
};

/**
 * Returns a flag indicating whether the InfoBox is visible.
 * @returns {boolean}
 */
InfoBox.prototype.getVisible = function () {

    var isVisible;

    if ((typeof this.getMap() === "undefined") || (this.getMap() === null)) {
        isVisible = false;
    } else {
        isVisible = !this.isHidden_;
    }
    return isVisible;
};

/**
 * Shows the InfoBox. [Deprecated; use <tt>setVisible</tt> instead.]
 */
InfoBox.prototype.show = function () {

    this.isHidden_ = false;
    if (this.div_) {
        this.div_.style.visibility = "visible";
    }
};

/**
 * Hides the InfoBox. [Deprecated; use <tt>setVisible</tt> instead.]
 */
InfoBox.prototype.hide = function () {

    this.isHidden_ = true;
    if (this.div_) {
        this.div_.style.visibility = "hidden";
    }
};

/**
 * Adds the InfoBox to the specified map or Street View panorama. If <tt>anchor</tt>
 *  (usually a <tt>google.maps.Marker</tt>) is specified, the position
 *  of the InfoBox is set to the position of the <tt>anchor</tt>. If the
 *  anchor is dragged to a new location, the InfoBox moves as well.
 * @param {Map|StreetViewPanorama} map
 * @param {MVCObject} [anchor]
 */
InfoBox.prototype.open = function (map, anchor) {

    var me = this;

    if (anchor) {

        this.position_ = anchor.getPosition();
        this.moveListener_ = google.maps.event.addListener(anchor, "position_changed", function () {
            me.setPosition(this.getPosition());
        });
    }

    this.setMap(map);

    if (this.div_) {

        this.panBox_();
    }
};

/**
 * Removes the InfoBox from the map.
 */
InfoBox.prototype.close = function () {

    var i;

    if (this.closeListener_) {

        google.maps.event.removeListener(this.closeListener_);
        this.closeListener_ = null;
    }

    if (this.eventListeners_) {

        for (i = 0; i < this.eventListeners_.length; i++) {

            google.maps.event.removeListener(this.eventListeners_[i]);
        }
        this.eventListeners_ = null;
    }

    if (this.moveListener_) {

        google.maps.event.removeListener(this.moveListener_);
        this.moveListener_ = null;
    }

    if (this.contextListener_) {

        google.maps.event.removeListener(this.contextListener_);
        this.contextListener_ = null;
    }

    this.setMap(null);
};
/*
 * EE_GMaps.js
 * http://reinos.nl
 *
 * Map types (Toner, Terrain and Watercolor) are Map tiles by Stamen Design, under CC BY 3.0. Data by OpenStreetMap, under CC BY SA
 *
 * @package            Gmaps for EE2
 * @author             Rein de Vries (info@reinos.nl)
 * @copyright          Copyright (c) 2013 Rein de Vries
 * @license 		   http://reinos.nl/commercial-license
 * @link               http://reinos.nl/add-ons/gmaps
 */
;
var EE_GMAPS = EE_GMAPS || {};


//check if jQuery is loaded
EE_GMAPS.jqueryLoaded = function(){
    if (typeof jQuery == 'undefined') {
        console.info('GMAPS ERROR: jQuery is not loaded. Make sure Jquery is loaded before Gmaps is called');
    }
}();

(function ($) {

    //default lat lng values
    EE_GMAPS.def = {};
    EE_GMAPS.vars = {}; //default vars, dynamic created by this file
    EE_GMAPS.def.lat = EE_GMAPS.def.Lat = -12.043333;
    EE_GMAPS.def.lng = EE_GMAPS.def.Lng = -77.028333;
    EE_GMAPS.def.circle = {
        'fit_circle': true,
        'stroke_color': '#BBD8E9',
        'stroke_opacity': 1,
        'stroke_weight': 3,
        'fill_color': '#BBD8E9',
        'fill_opacity': 0.6,
        'radius': 1000
    };

    //create the diff types of arrays
    var arrayTypes = ['polylines', 'polygons', 'circles', 'rectangles', 'markers'];
    $.each(arrayTypes, function (k, v) {
        EE_GMAPS[v] = [];
    });

    //marker holder
    EE_GMAPS.markers_address_based = {};
    EE_GMAPS.markers_key_based = {};

    //latlng holder
    EE_GMAPS.latlngs = [];

    //the map
    EE_GMAPS._map_ = [];

    //fitMap default to false
    EE_GMAPS.fitTheMap = false;

    //ready function, when this file is totally ready
    var funcList = [];
    EE_GMAPS.runAll = function () {
        var len = funcList.length,
            index = 0;

        for (; index < len; index++)
            funcList[index].call(); // you can pass in a "this" parameter here.
    };
    EE_GMAPS.ready = function (inFunc) {
        funcList.push(inFunc);
    };

    EE_GMAPS.cacheLists = function(lists) {
        $.each(lists, function(i, name) {
            var list = google.maps[name];
            var iList = {};
            $.each(list, function(k, v) {
                iList[v] = k;
            });

            EE_GMAPS[name] = list;
            EE_GMAPS[name + 'I'] = iList;
        });
    };

    //cache the Google maps options and also reverse them
    EE_GMAPS.cacheLists(['MapTypeControlStyle', 'ControlPosition']);

    //get latlong based on address
    EE_GMAPS.setGeocoding = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'map_type': '',
            'map_types': [],
            'input_address': [],
            'address': [],
            'latlng': [],
            'keys': [],
            'zoom': '',
            'zoom_override': false,
            'center': '',
            'width': '',
            'height': '',
            'loader': '',
            'marker': [],
            'static': true,
            'scroll_wheel': true,
            'zoom_control': true,
            'zoom_control_style' : '',
            'zoom_control_position' : '',
            'pan_control': true,
            'pan_control_position' : '',
            'map_type_control': true,
            'map_type_control_style' : '',
            'map_type_control_position' : '',
            'scale_control': true,
            'street_view_control': true,
            'street_view_control_position' : '',
            'show_elevation': false,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            //circle specific
            'circle': {
                'circle': [],
                'fit_circle': true,
                'stroke_color': options.stroke_color,
                'stroke_opacity': options.stroke_opacity,
                'stroke_weight': options.stroke_weight,
                'fill_color': options.fill_color,
                'fill_opacity': options.fill_opacity,
                'radius': options.radius
            },
            //end circle
            'hidden_div': '',
            'enable_new_style': true,
            'overlay_html' : '',
            'overlay_position' : ''
        }, options);

        //turn back the address, input_address and latlng
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.input_address = EE_GMAPS.parseToJsArray(options.input_address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);
        options.keys = EE_GMAPS.parseToJsArray(options.keys);
        options.center = EE_GMAPS.parseToJsArray(options.center);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //is this a static map?
        if (options.static) {
            EE_GMAPS.setStaticMap({
                'selector': options.selector,
                'latlng': options.latlng,
                'map_type': options.map_type,
                'width': options.width.match('%') ? '' : options.width,
                'height': options.height.match('%') ? '' : options.height,
                'zoom': options.zoom,
                'marker': options.marker.show
            });
            return true;
        }

        var circle = [];
        var circleBounds = new google.maps.LatLngBounds();

        //create latlng object
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            zoom: options.zoom,
            lat: latlng[0].lat(),
            lng: latlng[0].lng(),
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //todo add examples/overlay_map_types.html

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map, {
            tag: options.panoramio_tag
        });

        //set the marker
        var marker_icon,
            marker_title;
        //var address;

        //loop through the address
        $.each(latlng, function (k, v) {

            var address = options.address[k] ? options.address[k] : '';
            var location = options.address[k] ? options.address[k] : v.toString().replace('(', '').replace(')', '');

            //set the title
            if (options.marker.show_title) {
                marker_title = options.marker.title[k] == undefined ? location : options.marker.title[k];
            } else {
                marker_title = null;
            }

            //place marker
            if (options.marker.show) {

                marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon, options.marker.icon_default, k);
                marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow, options.marker.icon_default_shadow, k);
                marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape, options.marker.icon_shape_default, k);

                //set the custom marker
                /*if(options.marker_icon.length > 0) {
					marker_icon = options.marker_icon[k] ? options.marker_icon[k] : options.marker_icon_default;
				} else {
					marker_icon = options.marker_icon_default;
				}*/

                //get the elevation
                if (options.show_elevation) {
                    map.getElevations({
                        locations: EE_GMAPS.createlatLngArray([v]),
                        callback: function (result, status) {
                            if (status == google.maps.ElevationStatus.OK) {

                                //tmp var for the content
                                var tmp_content;

                                //custom marker
                                if (options.marker.custom_html.length > 0) {

                                    //set the content
                                    tmp_content = EE_GMAPS.setInfowindowContent(options.marker.custom_html[k], {
                                        'elevation': result[0].elevation.toFixed(2),
                                        'location': location
                                    }, v);
                                    //tmp_content = options.marker.custom_html[k] ? options.marker.custom_html[k].replace('[elevation]', result[0].elevation.toFixed(2)).replace('[location]', location) : '';

                                    //draw overlay
                                    map.drawOverlay({
                                        lat: v.lat(),
                                        lng: v.lng(),
                                        content: tmp_content,
                                        verticalAlign: options.marker.custom_html_vertical_align,
                                        horizontalAlign: options.marker.custom_html_vertical_align
                                    });

                                    //aslo add marker
                                    if(options.marker.custom_html_show_marker) {
                                        map.addMarker(EE_GMAPS.cleanObject({
                                            lat: v.lat(),
                                            lng: v.lng(),
                                            title: marker_title,
                                            label: options.marker.label[k] != undefined ? options.marker.label[k] : null,
                                            icon: marker_icon,
                                            shadow: marker_icon_shadow,
                                            shape: marker_icon_shape,
                                            animation: options.marker.animation ? google.maps.Animation.DROP : null
                                        }));
                                    }
                                } else {

                                    //set the content
                                    if (options.marker.html.length > 0) {

                                        tmp_marker_html_nr = options.marker.html[k] ? k : 0;

                                        //address set?
                                        if (options.address[k]) {

                                            tmp_content = EE_GMAPS.setInfowindowContent(options.marker.html[tmp_marker_html_nr], {
                                                'elevation': result[0].elevation.toFixed(2),
                                                'location': location
                                            }, v);

                                            //check if there is a default html content
                                            if(tmp_content === null) {
                                                tmp_content = EE_GMAPS.setInfowindowContent(options.marker.html_default, {
                                                    'elevation': result[0].elevation.toFixed(2),
                                                    'location': location
                                                }, v);
                                            }

                                            //tmp_content = options.marker.html[k] ? 
                                            //options.marker.html[k].replace('[elevation]', result[0].elevation.toFixed(2)).replace('[location]', location) : 
                                            //options.marker.html[0].replace('[elevation]', result[0].elevation.toFixed(2)).replace('[location]', location);
                                        } else {

                                            tmp_content = EE_GMAPS.setInfowindowContent(options.marker.html[tmp_marker_html_nr], {
                                                'elevation': result[0].elevation.toFixed(2)
                                            }, v);

                                            //check if there is a default html content
                                            if(tmp_content === null) {
                                                tmp_content = EE_GMAPS.setInfowindowContent(options.marker.html_default, {
                                                    'elevation': result[0].elevation.toFixed(2)
                                                }, v);
                                            }

                                            //tmp_content = options.marker.html[k] ? 
                                            //options.marker.html[k].replace('[elevation]', result[0].elevation.toFixed(2)) : 
                                            //options.marker.html[0].replace('[elevation]', result[0].elevation.toFixed(2));
                                        }
                                    }

                                    //Add marker
                                    map.addMarker(EE_GMAPS.cleanObject({
                                        lat: v.lat(),
                                        lng: v.lng(),
                                        title: marker_title,
                                        label: options.marker.label[k] != undefined ? options.marker.label[k] : null,
                                        icon: marker_icon,
                                        shadow: marker_icon_shadow,
                                        shape: marker_icon_shape,
                                        animation: options.marker.animation ? google.maps.Animation.DROP : null,
                                        infoWindow: {
                                            content: tmp_content
                                        }
                                    }));

                                    //set the infobox
                                    EE_GMAPS.addInfobox(options, map, map.markers[k], k);
                                }

                                //open first marker infowindow when there is one marker
                                if (latlng.length == 1 && options.marker.open_by_default) {
                                    google.maps.event.trigger(map.markers[0], 'click');
                                }
                            }
                        }
                    });
                } else {
                    //custom marker
                    if (options.marker.custom_html.length > 0) {
                        map.drawOverlay({
                            lat: v.lat(),
                            lng: v.lng(),
                            content: EE_GMAPS.setInfowindowContent(options.marker.custom_html[k], {
                                'location': location
                            }, v),
                            //options.marker.custom_html[k] ? options.marker.custom_html[k].replace('[location]', location) : '',
                            verticalAlign: options.marker.custom_html_vertical_align,
                            horizontalAlign: options.marker.custom_html_vertical_align
                        });

                        //also add marker
                        if(options.marker.custom_html_show_marker) {
                            map.addMarker(EE_GMAPS.cleanObject({
                                lat: v.lat(),
                                lng: v.lng(),
                                title: marker_title,
                                label: options.marker.label[k] != undefined ? options.marker.label[k] : null,
                                icon: marker_icon,
                                shadow: marker_icon_shadow,
                                shape: marker_icon_shape,
                                animation: options.marker.animation ? google.maps.Animation.DROP : null
                            }));
                        }
                    } else {

                        //set the html content
                        var html_content = EE_GMAPS.setInfowindowContent(options.marker.html[k], {
                            'location': location
                        }, v);

                        //check if there is a default html content
                        if(html_content === null) {
                            html_content = EE_GMAPS.setInfowindowContent(options.marker.html_default, {
                                'location': location
                            }, v);
                        }

                        map.addMarker(EE_GMAPS.cleanObject({
                            lat: v.lat(),
                            lng: v.lng(),
                            title: marker_title,
                            label: options.marker.label[k] != undefined ? options.marker.label[k] : null,
                            icon: marker_icon,
                            shadow: marker_icon_shadow,
                            shape: marker_icon_shape,
                            animation: options.marker.animation ? google.maps.Animation.DROP : null,
                            infoWindow: {
                                content: html_content
                            }
                        }));

                        //set the infobox
                        EE_GMAPS.addInfobox(options, map, map.markers[k], k);

                        //open first marker infowindow when there is one marker
                        if (latlng.length == 1 && options.marker.open_by_default) {
                            google.maps.event.trigger(map.markers[0], 'click');
                        }
                    }
                }
            }

            //set the circle
            if (options.circle.circle[k] || options.circle.circle[0] == 'all') {

                //create the circle
                circle[k] = map.drawCircle({
                    strokeColor: options.circle.stroke_color[k] ? options.circle.stroke_color[k] : EE_GMAPS.def.circle.stroke_color,
                    strokeOpacity: options.circle.stroke_opacity[k] ? options.circle.stroke_opacity[k] : EE_GMAPS.def.circle.stroke_opacity,
                    strokeWeight: options.circle.stroke_weight[k] ? options.circle.stroke_weight[k] : EE_GMAPS.def.circle.stroke_weight,
                    fillColor: options.circle.fill_color[k] ? options.circle.fill_color[k] : EE_GMAPS.def.circle.fill_color,
                    fillOpacity: options.circle.fill_opacity[k] ? options.circle.fill_opacity[k] : EE_GMAPS.def.circle.fill_opacity,
                    radius: options.circle.radius[k] ? parseInt(options.circle.radius[k]) : EE_GMAPS.def.circle.radius,
                    lat: v.lat(),
                    lng: v.lng()
                });

                //set the bounds for the circle
                circleBounds.union(circle[k].getBounds());
            }
        });

        // Clustering
        // @todo build it with the gmaps.js /examples/marker_clusterer.html
        if (options.marker.show_cluster) {
            var markerCluster = new MarkerClusterer(map.map, map.markers, {
                gridSize: options.marker.cluster_grid_size,
                maxZoom: 10,
                styles: options.marker.cluster_style,
                imagePath: EE_GMAPS.theme_path+'images/cluster/m'
            });
        }

        //fit the map
        if (circle.length > 0) {
            //fit the map
            if (options.circle.fit_circle) {
                map.fitBounds(circleBounds);
            }
        } else {
            //override center by setting the center or zoom level
            if (options.center != undefined && options.center != '') {
                options.center = options.center.toString().split(',');

                var center = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray([options.center]));

                map.setCenter(center[0].lat(), center[0].lng());
                map.setZoom(options.zoom);

                //zoom override
            } else if (options.zoom_override) {
                map.setZoom(options.zoom);

                //default
            } else if (latlng.length > 1) {
                map.fitLatLngBounds(latlng);
            }
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers, options.input_address, options.keys);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setGeolocation = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'width': '',
            'zoom': '',
            'map_type': '',
            'map_types': [],
            'height': '',
            'marker': [],
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            zoom: 1,
            lat: EE_GMAPS.def.lat,
            lng: EE_GMAPS.def.lng,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon);
        var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow);
        var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape);

        GMaps.geolocate({
            success: function (position) {
                map.setCenter(position.coords.latitude, position.coords.longitude);
                map.setZoom(options.zoom);

                //reverse geocode
                GMaps.geocode({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    callback: function (e) {
                        //place marker
                        if (options.marker.show) {
                            if (options.marker.custom_html != '') {
                                map.drawOverlay({
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude,
                                    content: EE_GMAPS.setInfowindowContent(options.marker.custom_html, {
                                        'location': e[0].formatted_address
                                    }, e[0].geometry.location),
                                    //options.marker.custom_html ? options.marker.custom_html.replace('[location]', e[0].formatted_address) : '',
                                    verticalAlign: options.marker.custom_html_vertical_align,
                                    horizontalAlign: options.marker.custom_html_vertical_align
                                });

                                //also add a marker
                                if(options.marker.custom_html_show_marker) {
                                    map.addMarker(EE_GMAPS.cleanObject({
                                        lat: position.coords.latitude,
                                        lng: position.coords.longitude,
                                        icon: marker_icon,
                                        shadow: marker_icon_shadow,
                                        shape: marker_icon_shape,
                                        animation: options.marker.animation ? google.maps.Animation.DROP : null,
                                        title: options.marker.show_title ? e[0].formatted_address : null
                                    }));
                                }

                            } else {

                                //set the html content
                                var html_content = EE_GMAPS.setInfowindowContent(options.marker.html, {
                                    'location': e[0].formatted_address
                                }, e[0].geometry.location);

                                //check if there is a default html content
                                if(html_content === null) {
                                    html_content = EE_GMAPS.setInfowindowContent(options.marker.html_default, {
                                        'location': e[0].formatted_address
                                    }, e[0].geometry.location);
                                }

                                map.addMarker(EE_GMAPS.cleanObject({
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude,
                                    icon: marker_icon,
                                    shadow: marker_icon_shadow,
                                    shape: marker_icon_shape,
                                    animation: options.marker.animation ? google.maps.Animation.DROP : null,
                                    title: options.marker.show_title ? e[0].formatted_address : null,
                                    infoWindow: {
                                        content: html_content
                                    }
                                }));

                                //set the infobox
                                EE_GMAPS.addInfobox(options, map, map.markers[0], 0);

                                //open the popup by default
                                if (options.marker.open_by_default) {
                                    google.maps.event.trigger(map.markers[0], 'click');
                                }
                            }
                        }
                    }
                });
            },
            error: function (error) {
                alert('Geolocation failed: ' + error.message);
            },
            not_supported: function () {
                alert("Your browser does not support geolocation");
            },
            always: function () {
                //alert("Done!");
            }
        });

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //Route address
    EE_GMAPS.setRoute = function (options) {

        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'from_address': '',
            'from_latlng': '',
            'to_address': '',
            'to_latlng': '',
            'stops_addresses': '',
            'stops_latlng': '',
            'map_type': '',
            'map_types': [],
            'travel_mode': "driving",
            'departure_time': new Date(),
            'arrival_time': null,
            'stroke_color': "#131540",
            'stroke_opacity': 0.6,
            'stroke_weight': 6,
            'marker': [],
            'width': '',
            'height': '',
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'show_details': false,
            'show_details_per_step': false,
            'details_per_step_template': '',
            'details_template': '',
            'show_elevation': false,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.from_address = EE_GMAPS.parseToJsArray(options.from_address, false);
        options.from_latlng = EE_GMAPS.parseToJsArray(options.from_latlng, false);
        options.to_address = EE_GMAPS.parseToJsArray(options.to_address, false);
        options.to_latlng = EE_GMAPS.parseToJsArray(options.to_latlng, false);
        options.stops_addresses = EE_GMAPS.parseToJsArray(options.stops_addresses);
        options.stops_latlng = EE_GMAPS.parseToJsArray(options.stops_latlng);

        //set map 
        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            lat: EE_GMAPS.def.lat,
            lng: EE_GMAPS.def.lng,
            zoom: 1,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //create the latlng object
        var from = EE_GMAPS.stringToLatLng(options.from_latlng);
        var stops = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.stops_latlng));
        var to = EE_GMAPS.stringToLatLng(options.to_latlng);

        var latlng = [from].concat(stops);
        latlng.push(to);
        var address_object = [options.from_address].concat(options.stops_addresses);
        address_object.push(options.to_address);

        //cache the locations for pinPoints purpose
        var markers = latlng.gmaps_clone();

        //create waypoints
        var waypoints = EE_GMAPS.createWaypoints(stops);

        //Transit?
        var transitOptions = {
            departureTime: options.departure_time,
            arrivalTime: options.arrival_time
        }

        //set the icons
        var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon, options.marker.icon_default, 0);
        var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow, options.marker.icon_default_shadow, 0);
        var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape, options.marker.icon_shape_default, 0);

        //set the icons
        //var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon);
        //var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow);
        //var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape);

        //draw route
        map.drawSteppedRoute({
            origin: [from.lat(), from.lng()],
            destination: [to.lat(), to.lng()],
            waypoints: waypoints,
            travelMode: options.travel_mode,
            transitOptions: transitOptions,
            strokeColor: options.stroke_color,
            strokeOpacity: options.stroke_opacity,
            strokeWeight: options.stroke_weight,
            start: function (e) {
                //show elevation by chart
                if (options.show_elevation) {

                    google.load("visualization", "1", {
                        packages: ["columnchart"],
                        callback: function () {
                            // Create a new chart in the elevation_chart DIV.
                            var _selector = options.selector + '_chart';
                            $(_selector).width(options.width);
                            chart = new google.visualization.ColumnChart(document.getElementById(_selector.replace('#', '')));

                            var tmp_locations = EE_GMAPS.createlatLngArray(e.overview_path);
                            map.getElevations({
                                path: true,
                                locations: tmp_locations,
                                callback: function (results, status) {
                                    if (status == google.maps.ElevationStatus.OK) {
                                        elevations = results;
                                        // Extract the data from which to populate the chart.
                                        // Because the samples are equidistant, the 'Sample'
                                        // column here does double duty as distance along the
                                        // X axis.
                                        var data = new google.visualization.DataTable();
                                        data.addColumn('string', 'Sample');
                                        data.addColumn('number', 'Elevation');
                                        for (var i = 0; i < results.length; i++) {
                                            data.addRow(['', elevations[i].elevation]);
                                        }
                                        // Draw the chart using the data within its DIV.
                                        chart.draw(data, {
                                            width: options.width,
                                            height: options.height / 2,
                                            legend: 'none',
                                            titleY: 'Elevation (m)',
                                            colors: [options.stroke_color]
                                        });

                                        //add a mouseover to set the new marker on the screen
                                        var mousemarker;
                                        google.visualization.events.addListener(chart, 'onmouseover', function (e) {
                                            if (mousemarker == null) {
                                                if (tmp_locations[e.row] != undefined) {
                                                    mousemarker = map.addMarker(EE_GMAPS.cleanObject({
                                                        lat: tmp_locations[e.row].lat(),
                                                        lng: tmp_locations[e.row].lng()
                                                        //icon : marker_icon,
                                                        //shadow : marker_icon_shadow,
                                                        //shape : marker_icon_shape
                                                    }));
                                                }
                                            } else {
                                                mousemarker.setPosition(elevations[e.row].location);
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    });
                }
            },
            step: function (e, total_steps) {
                
                //show the details
                if (options.show_details) {
                    //set the template
                    var details_per_step_template = options.details_per_step_template;
                    details_per_step_template = details_per_step_template
                        .replace('[instructions]', e.instructions)
                        .replace('[distance]', e.distance.text)
                        .replace('[duration]', e.duration.text);

                    if (e.transit) {
                        //default transit vars
                        details_per_step_template = details_per_step_template
                            .replace('[arrival_stop]', e.transit.arrival_stop)
                            .replace('[arrival_time]', e.transit.arrival_time)
                            .replace('[departure_stop]', e.transit.departure_stop)
                            .replace('[departure_time]', e.transit.departure_time)
                            .replace('[headsign]', e.transit.headsign)
                            .replace('[num_stops]', e.transit.num_stops)
                            .replace('[name]', e.transit.line.name)
                            .replace('[vehicle_icon]', e.transit.line.vehicle.icon)
                            .replace('[vehicle_name]', e.transit.line.vehicle.name)
                            .replace('[vehicle_type]', e.transit.line.vehicle.type);
                    }

                    $(options.selector + '_details_per_step').append('<li style="cursor:pointer;" rel="' + e.step_number + '">' + details_per_step_template + '</li>');
                    if (options.show_details_per_step) {
                        $(options.selector + '_details_per_step li[rel="' + e.step_number + '"]').click(function () {
                            //add active class
                            $(options.selector + '_details_per_step li').removeClass('active');
                            $(this).addClass('active');
                            //center map by fitbounds
                            map.fitLatLngBounds(e.lat_lngs);
                            //remove old markers
                            map.removeMarkers();
                            //add new markers
                            map.addMarker(EE_GMAPS.cleanObject({
                                lat: e.lat_lngs[0].lat(),
                                lng: e.lat_lngs[0].lng(),
                                //icon : marker_icon
                                //shadow : marker_icon_shadow,
                                //shape : marker_icon_shape,
                                animation: options.marker.animation ? google.maps.Animation.DROP : null
                            }));
                            map.addMarker(EE_GMAPS.cleanObject({
                                lat: e.lat_lngs[e.lat_lngs.length - 1].lat(),
                                lng: e.lat_lngs[e.lat_lngs.length - 1].lng(),
                                //icon : marker_icon,
                                //shadow : marker_icon_shadow,
                                //shape : marker_icon_shape,
                                animation: options.marker.animation ? google.maps.Animation.DROP : null
                            }));
                        });
                    }
                }
            },
            end: function (e) {
                //fit the map
                map.fitLatLngBounds(e.overview_path);

                //set the overal information
                if (options.show_details && e.legs[0]) {
                    var details_template = options.details_template;
                    details_template = details_template
                        .replace('[distance]', e.legs[0].distance.text)
                        .replace('[duration]', e.legs[0].duration.text)
                        .replace('[end_address]', e.legs[0].end_address)
                        .replace('[start_address]', e.legs[0].start_address);
                    $(options.selector + '_details').append(details_template);
                }
            },
            error: function(e) {
                console.log('Route cannot be generated');
            }
        });

        //fit the map, this is for the short time. In the end callback is the best fitBounds
        map.fitLatLngBounds(markers);

        //place the markers
        if (options.marker.show) {
            $.each(markers, function (k, v) {

                marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon, options.marker.icon_default, k);
                marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow, options.marker.icon_default_shadow, k);
                marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape, options.marker.icon_shape_default, k);
                //console.log(marker_icon_test, marker_icon_shadow_test, marker_icon_shape_test);

                var location = address_object[k] ? address_object[k] : v.toString().replace('(', '').replace(')', '');
                map.addMarker(EE_GMAPS.cleanObject({
                    lat: v.lat(),
                    lng: v.lng(),
                    title: options.marker.show_title ? location : null,
                    icon: marker_icon,
                    shadow: marker_icon_shadow,
                    shape: marker_icon_shape,
                    animation: options.marker.animation ? google.maps.Animation.DROP : null
                }));
            });
        }

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setPolygon = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'json': '',
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'stroke_color': '',
            'stroke_opacity': '',
            'stroke_weight': '',
            'fill_color': '',
            'fill_opacity': '',
            'marker': [],
            'static': true,
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

        //is this a static map?
        if (options.static) {
            EE_GMAPS.setStaticMap({
                'selector': options.selector,
                'latlng': options.latlng,
                'map_type': options.map_type,
                'width': options.width,
                'height': options.height,
                'zoom': options.zoom,
                'marker': options.marker,
                'polygon': true,
                'stroke_color': options.stroke_color,
                'stroke_opacity': options.stroke_opacity,
                'stroke_weight': options.stroke_weight,
                'fill_color': options.fill_color,
                'fill_opacity': options.fill_opacity
            });
            return true;
        }

        //set the icons
        var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon);
        var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow);
        var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape);


        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            lat: EE_GMAPS.def.lat,
            lng: EE_GMAPS.def.lng,
            zoom: 1,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //create the latlng
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        //is the latlng filled?
        if (latlng.length > 0) {
            var is_json = false;
            var _polygon = [];
            $.each(latlng, function (k, v) {
                _polygon.push([v.lat(), v.lng()]);
            });

            //We got json?
        } else if (options.json != '') {
            var is_json = true;
            var _polygon = JSON.parse(options.json);
        }

        //create the polygon
        var polygon = map.drawPolygon({
            paths: _polygon, // pre-defined polygon shape
            useGeoJSON: is_json,
            strokeColor: options.stroke_color,
            strokeOpacity: options.stroke_opacity,
            strokeWeight: options.stroke_weight,
            fillColor: options.fill_color,
            fillOpacity: options.fill_opacity
        });

        //fit the map
        //map.fitLatLngBounds(EE_GMAPS.flatten_polygon_result(polygon.getPaths()));
        map.fitBounds(polygon.getBounds());

        //place marker
        if (options.marker.show) {
            $.each(latlng, function (k, v) {
                var location = options.address[k] ? options.address[k] : v.toString().replace('(', '').replace(')', '');
                map.addMarker(EE_GMAPS.cleanObject({
                    lat: v.lat(),
                    lng: v.lng(),
                    title: options.marker.show_title ? location : null,
                    icon: marker_icon,
                    shadow: marker_icon_shadow,
                    shape: marker_icon_shape,
                    animation: options.marker.animation ? google.maps.Animation.DROP : null
                }));
            });
        }

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setPolyline = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'json': '',
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'stroke_color': '',
            'stroke_opacity': '',
            'stroke_weight': '',
            'marker': [],
            'static': true,
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

        //is this a static map?
        if (options.static) {
            EE_GMAPS.setStaticMap({
                'selector': options.selector,
                'latlng': options.latlng,
                'map_type': options.map_type,
                'width': options.width,
                'height': options.height,
                'zoom': options.zoom,
                'marker': options.marker,
                'polygon': true,
                'stroke_color': options.stroke_color,
                'stroke_opacity': options.stroke_opacity,
                'stroke_weight': options.stroke_weight,
                'fill_color': options.fill_color,
                'fill_opacity': options.fill_opacity
            });
            return true;
        }

        //set the icons
        var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon);
        var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow);
        var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape);

        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            lat: EE_GMAPS.def.lat,
            lng: EE_GMAPS.def.lng,
            zoom: 1,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //create the latlng
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        //set the polyline
        var _polyline = [];
        $.each(latlng, function (k, v) {
            _polyline.push([v.lat(), v.lng()]);
        });

        //create the polyline
        var polyline = map.drawPolyline({
            path: _polyline, // pre-defined polyline shape
            strokeColor: options.stroke_color,
            strokeOpacity: options.stroke_opacity,
            strokeWeight: options.stroke_weight
        });

        //fit the map
        //map.fitLatLngBounds(EE_GMAPS.flatten_polygon_result(polygon.getPaths()));
        map.fitBounds(polyline.getBounds());

        //place marker
        if (options.marker.show) {
            $.each(latlng, function (k, v) {
                var location = options.address[k] ? options.address[k] : v.toString().replace('(', '').replace(')', '');
                map.addMarker(EE_GMAPS.cleanObject({
                    lat: v.lat(),
                    lng: v.lng(),
                    title: options.marker.show_title ? location : null,
                    icon: marker_icon,
                    shadow: marker_icon_shadow,
                    shape: marker_icon_shape,
                    animation: options.marker.animation ? google.maps.Animation.DROP : null
                }));
            });
        }

        //set some vars
        EE_GMAPS.vars.polylineLenght = google.maps.geometry.spherical.computeLength(polyline.getPath());

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setCircle = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'marker': [],
            'zoom': '',
            //circle specific
            'fit_circle': '',
            'stroke_color': options.stroke_color,
            'stroke_opacity': options.stroke_opacity,
            'stroke_weight': options.stroke_weight,
            'fill_color': options.fill_color,
            'fill_opacity': options.fill_opacity,
            'radius': options.radius,

            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        options.address = options.address ? options.address : '';

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address, false);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng, false);

        //create latlng object
        var latlng = EE_GMAPS.stringToLatLng(options.latlng);

        //set the icons
        var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon);
        var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow);
        var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape);

        //create the map	
        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            lat: latlng.lat(),
            lng: latlng.lng(),
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            zoom: options.zoom,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //create the circle
        var circle = map.drawCircle({
            strokeColor: options.stroke_color,
            strokeOpacity: options.stroke_opacity,
            strokeWeight: options.stroke_weight,
            fillColor: options.fill_color,
            fillOpacity: options.fill_opacity,
            radius: options.radius,
            lat: latlng.lat(),
            lng: latlng.lng()
        });

        //fit the map
        if (options.fit_circle) {
            map.fitBounds(circle.getBounds());
        }

        //place marker 
        if (options.marker.show) {
            var location = options.address ? options.address : latlng.toString().replace('(', '').replace(')', '');
            if (options.marker.custom_html != '') {
                map.drawOverlay({
                    lat: latlng.lat(),
                    lng: latlng.lng(),
                    content: EE_GMAPS.setInfowindowContent(options.marker.custom_html, {
                        'location': location
                    }, latlng),
                    //options.marker.custom_html ? options.marker.custom_html.replace('[location]', location) : '',
                    verticalAlign: options.marker.custom_html_vertical_align,
                    horizontalAlign: options.marker.custom_html_vertical_align
                });

                //also add a marker
                if(options.marker.custom_html_show_marker) {
                    map.addMarker(EE_GMAPS.cleanObject({
                        lat: latlng.lat(),
                        lng: latlng.lng(),
                        icon: marker_icon,
                        shadow: marker_icon_shadow,
                        shape: marker_icon_shape,
                        animation: options.marker.animation ? google.maps.Animation.DROP : null,
                        title: options.marker.show_title ? location : null,
                        label: options.marker.label[0] != undefined ? options.marker.label[0] : null,
                    }));
                }

            } else {

                //set the html content
                var html_content = EE_GMAPS.setInfowindowContent(options.marker.html, {
                    'location': location
                }, latlng);

                //check if there is a default html content
                if(html_content === null) {
                    html_content = EE_GMAPS.setInfowindowContent(options.marker.html_default, {
                        'location': location
                    }, latlng);
                }

                map.addMarker(EE_GMAPS.cleanObject({
                    lat: latlng.lat(),
                    lng: latlng.lng(),
                    icon: marker_icon,
                    shadow: marker_icon_shadow,
                    shape: marker_icon_shape,
                    animation: options.marker.animation ? google.maps.Animation.DROP : null,
                    title: options.marker.show_title ? location : null,
                    label: options.marker.label[0] != undefined ? options.marker.label[0] : null,
                    infoWindow: {
                        content: html_content
                    }
                }));

                //set the infobox
                EE_GMAPS.addInfobox(options, map, map.markers[0], 0);

                //open the popup by default
                if (options.marker.open_by_default) {
                    google.maps.event.trigger(map.markers[0], 'click');
                }
            }
        }

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setRectangle = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'zoom': '',
            'stroke_color': options.stroke_color,
            'stroke_opacity': options.stroke_opacity,
            'stroke_weight': options.stroke_weight,
            'fill_color': options.fill_color,
            'fill_opacity': options.fill_opacity,
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            lat: EE_GMAPS.def.lat,
            lng: EE_GMAPS.def.lng,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            zoom: 1,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //create latlng object
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        //create rectangle
        var rectangle = map.drawRectangle({
            bounds: [
                [latlng[0].lat(), latlng[0].lng()],
                [latlng[1].lat(), latlng[1].lng()]
            ],
            strokeColor: options.stroke_color,
            strokeOpacity: options.stroke_opacity,
            strokeWeight: options.stroke_weight,
            fillColor: options.fill_color,
            fillOpacity: options.fill_opacity
        });

        //fit the map
        map.fitBounds(rectangle.getBounds());

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setPlaces = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'marker': [],
            'zoom': '',
            'radius': options.radius,
            'type': 'search', //radar_search also an option
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address, false);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng, false);

        //create latlng object
        var latlng = EE_GMAPS.stringToLatLng(options.latlng);

        var marker_icon = EE_GMAPS.setMarkerIcon(options.marker.icon);
        var marker_icon_shadow = EE_GMAPS.setMarkerIcon(options.marker.icon_shadow);
        var marker_icon_shape = EE_GMAPS.setMarkerShape(options.marker.icon_shape);

        //create the map
        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            lat: latlng.lat(),
            lng: latlng.lng(),
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            zoom: options.zoom,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //set the typ
        var type = 'search';
        if (options.type == 'radar_search') {
            type = 'radarSearch';
        }
        var search_options = {
            location: latlng,
            radius: options.radius,
            types: options.search_types,
            keyword: options.search_keyword
        };

        search_options[type] = function (results, status) {
            var bounds = [];
            if (status == google.maps.places.PlacesServiceStatus.OK) {
                for (var i = 0; i < results.length; i++) {
                    var place = results[i];
                    bounds.push(place.geometry.location);
                    map.addMarker(EE_GMAPS.cleanObject({
                        lat: place.geometry.location.lat(),
                        lng: place.geometry.location.lng(),
                        title: place.name,
                        icon: marker_icon,
                        shadow: marker_icon_shadow,
                        shape: marker_icon_shape,
                        animation: options.marker.animation ? google.maps.Animation.DROP : null,
                        infoWindow: {
                            content: '<h2>' + place.name + '</h2><p>' + (place.vicinity ? place.vicinity : place.formatted_address) + '</p><img src="' + place.icon + '"" width="100"/>'
                        }
                    }));

                    /*placesLayer.getDetails({
						reference : place.reference
					}, function (place_detail, status){
						map.createMarker(place_detail);
					});*/
                }
            }

            //fit the map
            if (bounds.length > 1) {
                map.fitLatLngBounds(bounds);
            } else if (bounds.length == 1) {
                map.setCenter(bounds[0].lat(), bounds[0].lng());
            }
        }

        var placesLayer = map.addLayer('places', search_options);

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //Get kml data in a map (BETA)
    EE_GMAPS.setKml = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'kml_url': '',
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'zoom': '',
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        if (options.latlng != '' && options.zoom != 0) {
            options.address = EE_GMAPS.parseToJsArray(options.address);
            options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

            //create latlng object
            var latlng = EE_GMAPS.stringToLatLng(options.latlng[0]);
            var lat = latlng.lat(),
                lng = latlng.lng();
        } else {
            var lat = EE_GMAPS.def.lat,
                lng = EE_GMAPS.def.lng;
        }

        infoWindow = new google.maps.InfoWindow({});
        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            lat: lat,
            lng: lng,
            zoom: options.zoom,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        map.loadFromKML({
            url: options.kml_url,
            suppressInfoWindows: true,
            preserveViewport: options.zoom != 0 ? true : false,
            events: {
                click: function (point) {
                    infoWindow.setContent(point.featureData.infoWindowHtml);
                    infoWindow.setPosition(point.latLng);
                    infoWindow.open(map.map);
                }
            }
        });

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //Get a fusion table (BETA)
    EE_GMAPS.setFusionTable = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'table_id': '',
            'address': '',
            'latlng': '',
            'styles': [],
            'heatmap': false,
            'map_type': '',
            'map_types': [],
            'width': '',
            'height': '',
            'zoom': '',
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address, false);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng, false);

        infoWindow = new google.maps.InfoWindow({});

        //create latlng object
        var latlng = EE_GMAPS.stringToLatLng(options.latlng);

        //create the map			
        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            lat: latlng.lat(),
            lng: latlng.lng(),
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            zoom: options.zoom,
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        var fusion = map.loadFromFusionTables({
            query: {
                from: options.table_id
            },
            styles: options.styles,
            suppressInfoWindows: true,
            events: {
                click: function (point) {
                    infoWindow.setContent(point.infoWindowHtml);
                    infoWindow.setPosition(point.latLng);
                    infoWindow.open(map.map);
                }
            },
            heatmap: {
                enabled: options.heatmap
            }
        });

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setStreetViewPanorama = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'address': '',
            'latlng': '',
            'width': '',
            'height': '',
            'address_control': true,
            'click_to_go': true,
            'disable_double_click_zoom': false,
            'enable_close_button': true,
            'image_date_control': true,
            'links_control': true,
            'pan_control': true,
            'scroll_wheel': true,
            'zoom_control': true,
            'checkaround' : 50,
            'visible': true,
            'pov': {},
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

        //create latlng object
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        var map;
        EE_GMAPS._map_[options.selector] = map = GMaps.createPanorama({
            el: options.selector,
            lat: latlng[0].lat(),
            lng: latlng[0].lng(),
            addressControl: options.address_control,
            clickToGo: options.click_to_go,
            disableDoubleClickZoom: options.disable_double_click_zoom,
            enableCloseButton: options.enable_close_button,
            imageDateControl: options.image_date_control,
            linksControl: options.links_control,
            panControl: options.pan_control,
            pov: options.pov,
            scrollwheel: options.scroll_wheel,
            visible: options.visible,
            zoomControl: options.zoom_control,
            enableNewStyle: options.enable_new_style,
            checkaround: options.checkaround
        });

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setEmptyMap = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'width': '',
            'latlng': '',
            'address': '',
            'zoom': '',
            'map_type': '',
            'map_types': [],
            'height': '',
            'marker': [],
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //turn back base64 to js array
        options.address = EE_GMAPS.parseToJsArray(options.address);
        options.latlng = EE_GMAPS.parseToJsArray(options.latlng);

        //create latlng object
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));

        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps({
            el: options.selector,
            zoom: 1,
            lat: latlng[0].lat(),
            lng: latlng[0].lng(),
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style
        });

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        if (latlng.length > 1) {
            map.fitLatLngBounds(latlng);
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        EE_GMAPS.saveMarkers(options.selector, map.markers);
    };

    // ----------------------------------------------------------------------------------

    //get latlong based on address
    EE_GMAPS.setMap = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'width': '',
            'zoom': '',
            'map_type': '',
            'map_types': [],
            'height': '',
            'marker': [],
            'scroll_wheel': true,
            'zoom_control': true,
            'pan_control': true,
            'map_type_control': true,
            'scale_control': true,
            'street_view_control': true,
            'styled_map': '',
            'show_traffic': false,
            'show_transit': false,
            'show_bicycling': false,
            'show_weather': false,
            'show_panoramio': false,
            'panoramio_tag': '',
            'hidden_div': '',
            'enable_new_style': true,
            'marker_cluster': false
        }, options);

        //set the width for the div
        $(options.selector).css({
            width: options.width,
            height: options.height
        });

        //map options
        var map_options = {
            el: options.selector,
            zoom: 1,
            lat: EE_GMAPS.def.lat,
            lng: EE_GMAPS.def.lng,
            width: options.width,
            height: options.height,
            mapTypeControlOptions: {
                mapTypeIds: options.map_types
            },
            mapType: options.map_type,
            scrollwheel: options.scroll_wheel,
            zoomControl: options.zoom_control,
            zoomControlOptions: {
                style: google.maps.ZoomControlStyle[options.zoom_control_style],
                position: google.maps.ControlPosition[options.zoom_control_position]
            },
            panControl: options.pan_control,
            panControlOptions: {
                position: google.maps.ControlPosition[options.pan_control_position]
            },
            mapTypeControl: options.map_type_control,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle[options.map_type_control_style],
                position: google.maps.ControlPosition[options.map_type_control_position]
            },
            scaleControl: options.scale_control,
            streetViewControl: options.street_view_control,
            streetViewControlOptions: {
                position: google.maps.ControlPosition[options.street_view_control_position]
            },
            enableNewStyle: options.enable_new_style,

        };

        //marker cluster
        if (options.marker_cluster) {
            map_options.markerClusterer = function (map) {
                return new MarkerClusterer(map, [], {
                    gridSize: 60,
                    maxZoom: 10,
                    styles: options.marker_cluster_style,
                    imagePath: EE_GMAPS.theme_path+'images/cluster/m'
                });
            };
        };

        var map;
        EE_GMAPS._map_[options.selector] = map = new GMaps(map_options);

        //Add maptypes
        EE_GMAPS.addCustomMapTypes(map, options.map_types);

        //set an custom map if he is setted
        EE_GMAPS.addCustomMapType(map, options.map_type);

        // set the styled map
        EE_GMAPS.setStyledMap(options.styled_map, map);

        //set the layers
        EE_GMAPS.setTraffic(options.show_traffic, map);
        EE_GMAPS.setTransit(options.show_transit, map);
        EE_GMAPS.setBicycling(options.show_bicycling, map);
        EE_GMAPS.setWeather(options.show_weather, map);
        EE_GMAPS.setPaoramio(options.show_panoramio, map);

        //is there an hidden div situation?
        //https://github.com/HPNeo/gmaps/issues/53
        if ($(options.hidden_div).length > 0) {
            $(options.hidden_div).on('show', function () {
                map.refresh();
            });
        }

        //Add google like overlay
        EE_GMAPS.addGoogleOverlay(map, options);

        //focus on the users current location
        if(options.focus_current_location) {
            EE_GMAPS.geolocate(function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
            });
        }

        //set the markers
        //EE_GMAPS.markers = map.markers;
        //EE_GMAPS.saveMarkers(options.selector, map.markers);
        //callback function when all things is ready
        EE_GMAPS.runAll();
    };

    //----------------------------------------------------------------------------------------------------------//
    // Private functions //
    //----------------------------------------------------------------------------------------------------------//

    //get latlong based on address
    EE_GMAPS.setStaticMap = function (options) {
        //merge default settings with given settings
        var options = $.extend({
            'selector': '',
            'latlng': '',
            'map_type': '',
            'width': '',
            'height': '',
            'zoom': '',
            'marker': true,
            'polygon': false,
            'stroke_color': '',
            'stroke_opacity': '',
            'stroke_weight': '',
            'fill_color': '',
            'fill_opacity': ''
        }, options);

        // create latlng object
        var latlng = EE_GMAPS.arrayToLatLng(EE_GMAPS.cleanArray(options.latlng));
        var _polygon_latlng = [];
        var _markers = [];
        var bounds = new google.maps.LatLngBounds();

        //set the bounds and the polygon latlng
        $.each(latlng, function (k, v) {
            bounds.extend(v);

            _polygon_latlng.push([v.lat(), v.lng()]);

            if (options.marker.show) {
                _markers.push({
                    lat: v.lat(),
                    lng: v.lng()
                });
            }
        });

        //get the center
        var center = bounds.getCenter();

        //size
        if (options.width == '' || options.height) {
            options.width = '630';
            options.height = '300';
        }

        if (options.polygon) {

            //var center = EE_GMAPS.getCenterLatLng(latlng);
            //create map
            var url = GMaps.staticMapURL({
                size: [options.width, options.height],
                lat: center[0],
                lng: center[1],
                zoom: EE_GMAPS.getZoom(options.width, latlng),
                maptype: options.map_type,
                polyline: {
                    path: _polygon_latlng,
                    strokeColor: options.stroke_color,
                    strokeOpacity: options.stroke_opacity,
                    strokeWeight: options.stroke_weight,
                    fillColor: options.fill_color
                },
                markers: _markers
            });

            //geocoding
        } else {
            //create map
            var url = GMaps.staticMapURL({
                size: [options.width, options.height],
                lat: center.lat(),
                lng: center.lng(),
                zoom: options.zoom,
                maptype: options.map_type,
                markers: _markers
            });
        }

        //place the image
        $(options.selector).html('<img src="' + url + '" alt="Gmaps map from ' + options.address + '" title="Static Gmaps" width="' + options.width + '" height="' + options.height + '" />');
    };

    //add a google like overlay like the iframe
    EE_GMAPS.addGoogleOverlay = function(map, options, direct){
 
        var latlng, marker_object;

        if( options.latlng != undefined && options.latlng[0] != undefined) {
            latlng = options.latlng[0].split(',');
            marker_object = new google.maps.LatLng(latlng[0], latlng[1]);

             options.overlay_html = options.overlay_html.gmaps_replaceAll('[route_to_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'route_to'));
             options.overlay_html = options.overlay_html.gmaps_replaceAll('[route_from_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'route_from'));
             options.overlay_html = options.overlay_html.gmaps_replaceAll('[map_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'map'));
        }
     
        if(options.overlay_html != '') {
            if(direct) {
                if($(options.selector).find('#custom_gmaps_overlay').length == 0) {
                    var style = "margin: 10px; padding: 1px; -webkit-box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; border-top-left-radius: 2px; border-top-right-radius: 2px; border-bottom-right-radius: 2px; border-bottom-left-radius: 2px; background-color: white;";
                    $(options.selector).find('.gm-style').append('<div class="google-like-overlay-position" style="position: absolute; '+options.overlay_position+': 0px; top: 0px;"><div style="'+style+'" id="custom_gmaps_overlay"><div style="padding:5px;" class="place-card google-like-overlay-content place-card-large">'+options.overlay_html+'</div></div></div>');                         
                }
            }

            map.on('tilesloaded', function(){
                if($(options.selector).find('#custom_gmaps_overlay').length == 0) {
                    var style = "margin: 10px; padding: 1px; -webkit-box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; border-top-left-radius: 2px; border-top-right-radius: 2px; border-bottom-right-radius: 2px; border-bottom-left-radius: 2px; background-color: white;";
                    $(options.selector).find('.gm-style').append('<div class="google-like-overlay-position" style="position: absolute; '+options.overlay_position+': 0px; top: 0px;"><div style="'+style+'" id="custom_gmaps_overlay"><div style="padding:5px;" class="place-card google-like-overlay-content place-card-large">'+options.overlay_html+'</div></div></div>');                         
                }
            });
        }
    };

    //remove a google like overlay like the iframe
    EE_GMAPS.removeGoogleOverlay = function(selector){
        $(selector).find('.google-like-overlay-position').remove();
    }

    //update a google like overlay like the iframe
    EE_GMAPS.updateGoogleOverlay = function(map, options){
        //no overlay?
        if($(options.selector).find('.google-like-overlay-position').length == 0) {
            EE_GMAPS.addGoogleOverlay(map, options, true);
        }

        if(options.overlay_html != undefined) {
            if(options.overlay_html == '') {
                EE_GMAPS.removeGoogleOverlay(options.selector);
            } else {
                $(options.selector).find('.google-like-overlay-content').html(options.overlay_html);
            }
            
        }

        if(options.overlay_position != undefined) {
            $(options.selector).find('.google-like-overlay-position').css('left', '').css('right', '');
            $(options.selector).find('.google-like-overlay-position').css(options.overlay_position, '0px');
        }
    }

    //add mapTypes
    EE_GMAPS.addCustomMapTypes = function (map, map_types) {
        $.each(map_types, function (k, v) {
            switch (v) {
                //Openstreetmap
            case 'osm':
                map.addMapType("osm", {
                    getTileUrl: function (coord, zoom) {
                        return "http://tile.openstreetmap.org/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
                    },
                    tileSize: new google.maps.Size(256, 256),
                    name: "OpenStreetMap",
                    maxZoom: 18
                });
                break;

                //Cloudmade
            case 'cloudmade':
                map.addMapType("cloudmade", {
                    getTileUrl: function (coord, zoom) {
                        return "http://b.tile.cloudmade.com/8ee2a50541944fb9bcedded5165f09d9/1/256/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
                    },
                    tileSize: new google.maps.Size(256, 256),
                    name: "CloudMade",
                    maxZoom: 18
                });
                break;

                //StamenMapType: Toner
            case 'toner':
                map.map.mapTypes.set("toner", new google.maps.StamenMapType("toner"));
                break;

                //StamenMapType: watercolor
            case 'watercolor':
                map.map.mapTypes.set("watercolor", new google.maps.StamenMapType("watercolor"));
                break;
            };
        });
    };

    //Set custom maps
    EE_GMAPS.addCustomMapType = function (map, map_type) {
        switch (map_type) {
            //Openstreetmap
        case 'osm':
            map.setMapTypeId("osm");
            break;

            //Cloudmade
        case 'cloudmade':
            map.setMapTypeId("cloudmade");
            break;

            //StamenMapType: Toner
        case 'toner':
            map.setMapTypeId("toner");
            break;

            //StamenMapType: watercolor
        case 'watercolor':
            map.setMapTypeId("watercolor");
            break;
        };
    };

    //get latlong based on address
    EE_GMAPS.latLongAddress = function (addresses, callback) {

        var latLongAdresses = new Array();
        var latLongObject = new Array();

        $.each(addresses, function (key, val) {
            GMaps.geocode({
                address: val,
                callback: function (results, status) {
                    //is there any result
                    if (status == "OK") {
                        latLongAdresses[key] = results[0].geometry.location;
                        latLongObject[key] = results[0];
                    }

                    //return the results
                    if (key == (addresses.length - 1)) {
                        if (callback && typeof (callback) === "function") {
                            //settimeout because the load error
                            setTimeout(function () {
                                callback(latLongAdresses, latLongObject);
                            }, 200);
                        }
                    }
                }
            });
        });
    };

    //flatten an polygon result
    EE_GMAPS.flatten_polygon_result = function (polygon) {
        var new_array = [];

        polygon.getArray().forEach(function (v) {
            new_array.push(v.getArray());
        });

        return _.flatten(new_array);
    };

    //retrun good waypoints array based on some latlong positions
    EE_GMAPS.getCenterLatLng = function (latlong) {
        var lat = [];
        var lng = [];

        $.each(latlong, function (key, val) {
            lat.push(val.lat());
            lng.push(val.lng());
        });

        //set the center x and y points
        var center_lat = lat.gmaps_min() + ((lat.gmaps_max() - lat.gmaps_min()) / 2);
        var center_lng = lng.gmaps_min() + ((lng.gmaps_max() - lng.gmaps_min()) / 2);

        return [center_lat, center_lng];
    };

    //set the styled maps for a map
    EE_GMAPS.setStyledMap = function (styledArray, map) {
        if (Object.keys(styledArray).length > 0) {
            map.addStyle({
                styledMapName: "Styled Map",
                styles: styledArray,
                mapTypeId: "map_style"
            });
            map.setStyle("map_style");
        };
    };

    //set the traffic layer
    EE_GMAPS.setTraffic = function (show_layer, map) {
        if (show_layer) {
            map.addLayer('traffic');
        };
    };

    //set the Transit layer
    EE_GMAPS.setTransit = function (show_layer, map) {
        if (show_layer) {
            map.addLayer('transit');
        };
    };

    //set the Bicycling layer
    EE_GMAPS.setBicycling = function (show_layer, map) {
        if (show_layer) {
            map.addLayer('bicycling');
        };
    };

    //set the Weather layer
    EE_GMAPS.setWeather = function (show_layer, map) {
        if (show_layer) {
            map.addLayer('clouds');
            map.addLayer('weather');
        };
    };

    //set the Panoramio layer
    EE_GMAPS.setPaoramio = function (show_layer, map, options) {
        if (show_layer) {
            map.addLayer('panoramio', {
                filter: options.tag
            });
        };
    };

    //calculate the zoom
    EE_GMAPS.getZoom = function (map_width, latlong) {
        map_width = map_width / 1.74;
        var lat = [];
        var lng = [];

        $.each(latlong, function (key, val) {
            lat.push(val.lat());
            lng.push(val.lng());
        });

        //calculate the distance
        var dist = (6371 * Math.acos(Math.sin(lat.gmaps_min() / 57.2958) * Math.sin(lat.gmaps_max() / 57.2958) +
            (Math.cos(lat.gmaps_min() / 57.2958) * Math.cos(lat.gmaps_max() / 57.2958) * Math.cos((lng.gmaps_max() / 57.2958) - (lng.gmaps_min() / 57.2958)))));

        //calculate the zoom
        var zoom = Math.floor(8 - Math.log(1.6446 * dist / Math.sqrt(2 * (map_width * map_width))) / Math.log(2)) - 1;

        return zoom;
    };

    //retrun good waypoints array based on some latlong positions
    EE_GMAPS.createWaypoints = function (waypoints) {
        var points = [];
        $.each(waypoints, function (key, val) {
            points.push({
                location: val,
                stopover: false
            });
        });
        return points;
    };

    //retrun good waypoints array based on some latlong positions
    EE_GMAPS.createlatLngArray = function (latlng) {
        var points = [];
        $.each(latlng, function (key, val) {
            points.push([val.lat(), val.lng()]);
        });

        return points;
    };

    //reparse an array that whas generated by php
    EE_GMAPS.reParseLatLngArray = function (array) {
        var points = [];
        $.each(array, function (key, val) {
            points.push([parseFloat(val[0]), parseFloat(val[1])]);
        });
        return points;
    };

    //convert array to latlng 
    EE_GMAPS.arrayToLatLng = function (coords) {
        var new_coords = [];
        $.each(coords, function (key, val) {
            if (typeof val == 'string') {
                val = val.split(',');
            }
            new_coords.push(new google.maps.LatLng(parseFloat($.trim(val[0])), parseFloat($.trim(val[1]))));
        });
        return new_coords;
    };

    //convert string to latlng
    EE_GMAPS.stringToLatLng = function (coords) {
        var val = coords.split(',');
        var new_coords = new google.maps.LatLng(parseFloat($.trim(val[0])), parseFloat($.trim(val[1])));
        return new_coords;
    };

    //remove empty values
    EE_GMAPS.cleanArray = function (arr) {
        return $.grep(arr, function (n) {
            return (n);
        });
    };

    //Parse base64 string to js array
    EE_GMAPS.parseToJsArray = function (string, split) {
        string = base64_decode(string);
        if (typeof string == 'string') {

            //empty?
            if(string == '[]') {
                 return '';
            };

            string = decodeURIComponent(escape(string));
            if (split || split == undefined) {
                return string.split('|');
            } else {
                return string;
            };
        };
        return '';
    };

    //set the marker Icon 
    EE_GMAPS.setMarkerIcon = function (marker_icon, marker_icon_default, k) {

        //set vars
        var new_marker_icon, url, size, origin, anchor;

        //array of values, mostly geocoding
        if (typeof marker_icon.url == 'object' && marker_icon.url.length > 0) {
            url = marker_icon.url[k] != undefined ? marker_icon.url[k] : marker_icon_default.url;
            size = marker_icon.size[k] != undefined ? marker_icon.size[k] : marker_icon_default.size;
            size = size.split(',');
            origin = marker_icon.origin[k] != undefined ? marker_icon.origin[k] : marker_icon_default.origin;
            origin = origin.split(',');
            anchor = marker_icon.anchor[k] != undefined ? marker_icon.anchor[k] : marker_icon_default.anchor;
            anchor = anchor.split(',');

            //set the object
            new_marker_icon = {};
            if (url != '') {
                new_marker_icon.url = url;
                if (size != '') {
                    new_marker_icon.size = new google.maps.Size(parseInt(size[0]), parseInt(size[1]));
                }
                if (origin != '') {
                    new_marker_icon.origin = new google.maps.Point(parseInt(origin[0]), parseInt(origin[1]));
                }
                if (anchor != '') {
                    new_marker_icon.anchor = new google.maps.Point(parseInt(anchor[0]), parseInt(anchor[1]));
                }
            } else {
                new_marker_icon = '';
            }

            //default, all others beside geocoding
        } else if (marker_icon_default == undefined) {
            url = marker_icon.url;
            size = marker_icon.size;
            size = size.split(',');
            origin = marker_icon.origin;
            origin = origin.split(',');
            anchor = marker_icon.anchor;
            anchor = anchor.split(',');

            //set the object
            new_marker_icon = {};
            if (url != '') {
                new_marker_icon.url = url;
                if (size != '') {
                    new_marker_icon.size = new google.maps.Size(parseInt(size[0]), parseInt(size[1]));
                }
                if (origin != '') {
                    new_marker_icon.origin = new google.maps.Point(parseInt(origin[0]), parseInt(origin[1]));
                }
                if (anchor != '') {
                    new_marker_icon.anchor = new google.maps.Point(parseInt(anchor[0]), parseInt(anchor[1]));
                }
            } else {
                new_marker_icon = '';
            }

            //default marker icon, mostly geocoding
        } else {
            if (marker_icon_default.url != '') {
                url = marker_icon_default.url;
                size = marker_icon_default.size;
                size = size.split(',');
                origin = marker_icon_default.origin;
                origin = origin.split(',');
                anchor = marker_icon_default.anchor;
                anchor = anchor.split(',');

                //set the object
                new_marker_icon = {};
                if (url != '') {
                    new_marker_icon.url = url;
                    if (size != '') {
                        new_marker_icon.size = new google.maps.Size(parseInt(size[0]), parseInt(size[1]));
                    }
                    if (origin != '') {
                        new_marker_icon.origin = new google.maps.Point(parseInt(origin[0]), parseInt(origin[1]));
                    }
                    if (anchor != '') {
                        new_marker_icon.anchor = new google.maps.Point(parseInt(anchor[0]), parseInt(anchor[1]));
                    }
                } else {
                    new_marker_icon = '';
                }

                //no marker set? just empty
            } else {
                new_marker_icon = '';
            }
        }
        return new_marker_icon;
    };

    //set the marker shape 
    EE_GMAPS.setMarkerShape = function (marker_icon_shape, marker_icon_shape_default, k) {

        //set vars
        var new_marker_icon_shape, coord, type;

        //array of values, mostly geocoding
        if (typeof marker_icon_shape.coord == 'object' && marker_icon_shape.coord.length > 0) {
            coord = marker_icon_shape.coord[k] != undefined ? marker_icon_shape.coord[k] : marker_icon_shape_default.coord;
            type = marker_icon_shape.type[k] != undefined ? marker_icon_shape.type[k] : marker_icon_shape_default.type;

            //set the object
            new_marker_icon_shape = {};
            if (type != '') {
                new_marker_icon_shape.type = type;
            }
            if (coord != '') {
                new_marker_icon_shape.coord = coord.split(',');
            } else {
                new_marker_icon_shape = '';
            }

            //default, all others beside geocoding
        } else if (marker_icon_shape_default == undefined) {
            coord = marker_icon_shape.coord;
            type = marker_icon_shape.type;

            //set the object
            new_marker_icon_shape = {};
            if (type != '') {
                new_marker_icon_shape.type = type;
            }
            if (coord != '') {
                new_marker_icon_shape.coord = coord.split(',');
            } else {
                new_marker_icon_shape = '';
            }

            //default shape, mostly geocoding
        } else {
            if (marker_icon_shape_default.url != '') {
                coord = marker_icon_shape_default.coord;
                type = marker_icon_shape_default.type;

                //set the object
                new_marker_icon_shape = {};
                if (type != '') {
                    new_marker_icon_shape.type = type;
                }
                if (coord != '') {
                    new_marker_icon_shape.coord = coord.split(',');
                } else {
                    new_marker_icon_shape = '';
                }

                //no marker set? just empty
            } else {
                new_marker_icon_shape = '';
            }
        }
        return new_marker_icon_shape;
    };

    //set the infowindow content
    //and replace the tokens
    EE_GMAPS.setInfowindowContent = function (content, tokens, marker_object) {
        var content = content || '';

        if (content != undefined || content) {
            $.each(tokens, function (k, v) {
                content = content.gmaps_replaceAll('[' + k + ']', v);
            });

            //try creating the urls
            content = content.gmaps_replaceAll('[route_to_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'route_to'));
            content = content.gmaps_replaceAll('[route_from_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'route_from'));
            content = content.gmaps_replaceAll('[map_url]', EE_GMAPS.setInfowindowUrl(marker_object, 'map'));
        }

        //set content to null when empty
        content = content != '' ? content : null;

        return content;
    };

    //remove empty properties from an object
    EE_GMAPS.cleanObject = function (object) {
        var object = object || {};

        object = gmaps_remove_empty_values(object);

        return object;
    };

    //create the infobox
    EE_GMAPS.addInfobox = function(options, map, marker, marker_number){
        if(options.marker.infobox.content !== '') {
            var content = options.marker.infobox.content.split('|');
            var location = options.address[marker_number] ? options.address[marker_number] : marker.position.toString().replace('(', '').replace(')', '');
            //remove the hash
            var selector = options.selector.replace('#', '');

            //set the content
            content = EE_GMAPS.setInfowindowContent(content[marker_number], {
                'location': location
            }, marker.position);

            marker.infobox_options = {
                boxClass: options.marker.infobox.box_class,
                maxWidth: options.marker.infobox.max_width,
                zIndex: options.marker.infobox.z_index,
                content: content,
                pixelOffset: new google.maps.Size(parseInt(options.marker.infobox.pixel_offset.width), parseInt(options.marker.infobox.pixel_offset.height)),
                boxStyle: options.marker.infobox.box_style,
                closeBoxMargin: "10px 2px 2px 2px",
                closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif"
            };

            //create a infobox
            marker.infoBox = new InfoBox(marker.infobox_options);

            //save the marker
            EE_GMAPS.saveMarker(selector, marker);

            marker = EE_GMAPS.searchMarker(selector, marker_number);

            google.maps.event.addListener(marker, "click", function () {
                $.each(EE_GMAPS.markers[selector], function(i, _marker){
                    _marker.marker.infoBox.close();
                });

                marker.infoBox.open(map.map, marker);
            });
        }

    };

    //set the googlemaps url e.g. route or place
    EE_GMAPS.setInfowindowUrl = function (marker_object, type) {
        var url = '';
        if (marker_object != undefined) {
            switch (type) {
            case 'route_to':
                url = 'https://maps.google.com/maps?daddr=' + marker_object.lat() + ',' + marker_object.lng();
                //http://maps.google.com/maps?saddr=start&daddr=end
                break;

            case 'route_from':
                url = 'https://maps.google.com/maps?saddr=' + marker_object.lat() + ',' + marker_object.lng();
                //http://maps.google.com/maps?saddr=start&daddr=end
                break;

            default:
            case 'map':
                url = 'https://maps.google.com/maps?q=' + marker_object.lat() + ',' + marker_object.lng();
                //https://maps.google.com/maps?q=
                break;
            }
        }
        return url;
    };

    //set the markers to the arrays
    EE_GMAPS.saveMarkers = function (mapID, markers, address_based, keys) {

        //set mapID
        mapID = mapID.replace('#', '');
        //set vars
        var markerNumbers = [];
        var newMarkerData = [];

        if (markers.length > 0) {

            //save all to a latlng array
            $.each(markers, function (k, v) {
                //set the marker number
                v.markerNumber = k;
                //set the uuuid
                v.markerUUID = createUUID(),

                markerNumbers.push(v.markerNumber);

                //set the arrays
                newMarkerData[k] = [];
                newMarkerData[k]['marker'] = v;
                newMarkerData[k]['keys'] = [k, v.markerUUID, v.getPosition().lat() + ',' + v.getPosition().lng()];

                //save marker to array
                //EE_GMAPS.markers[k]['index'] = [v];

                //save all to a latlng array
                EE_GMAPS.latlngs.push(v.position.lat() + ',' + v.position.lng());
            });

            //create address based array
            if (typeof address_based == 'object') {
                $.each(address_based, function (k, v) {
                    if (newMarkerData[k] != undefined && newMarkerData[k]['keys'] != undefined) {
                        v = $.trim(v);
                        newMarkerData[k]['keys'].push(v);
                        newMarkerData[k]['keys'].push(v.toLowerCase());
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '_'));
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '_').toLowerCase());
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '-'));
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '-').toLowerCase());
                        //remove duplicated
                        newMarkerData[k]['keys'] = _.uniq(newMarkerData[k]['keys']);
                    }
                });
            };

            //create the custom keys for the marker
            if (typeof keys == 'object') {
                $.each(keys, function (k, v) {
                    if (newMarkerData[k] != undefined && newMarkerData[k]['keys'] != undefined) {
                        v = $.trim(v);
                        newMarkerData[k]['keys'].push(v);
                        newMarkerData[k]['keys'].push(v.toLowerCase());
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '_'));
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '_').toLowerCase());
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '-'));
                        newMarkerData[k]['keys'].push(v.gmaps_replaceAll(' ', '-').toLowerCase());
                        //remove duplicated
                        newMarkerData[k]['keys'] = _.uniq(newMarkerData[k]['keys']);
                    }
                });
            };

            //save the marker data
            EE_GMAPS.markers[mapID] = newMarkerData;
        };

        //callback function when all things is ready
        EE_GMAPS.runAll();

        return markerNumbers.length == 1 ? markerNumbers[0] : markerNumbers;
    };

    //save single marker
    EE_GMAPS.saveMarker = function (mapID, marker) {
        //set the map array
        if (EE_GMAPS.markers[mapID] == undefined) {
            EE_GMAPS.markers[mapID] = [];
        };
        //get the index
        var index = EE_GMAPS.markers[mapID].length;
        //set markerNumber
        marker.markerNumber = index;
        //set the uuuid
        marker.markerUUID = createUUID();
        //set the arrays
        EE_GMAPS.markers[mapID][index] = [];
        EE_GMAPS.markers[mapID][index]['marker'] = marker;
        EE_GMAPS.markers[mapID][index]['keys'] = [index, marker.markerUUID];
        //update lnglngs array
        EE_GMAPS.latlngs.push(marker.position.lat() + ',' + marker.position.lng());

        return marker.markerNumber;
    };

    //set the markers to the arrays
    EE_GMAPS.searchMarker = function (mapID, marker_name) {

        var marker;

        //loop over the markers
        if(EE_GMAPS.markers[mapID] != undefined) {
            $.each(EE_GMAPS.markers[mapID], function (key, val) {
                //search the array
                if (val['keys'] != undefined) {
                    if (jQuery.inArray(marker_name, val['keys']) != -1) {
                        marker = EE_GMAPS.markers[mapID][key]['marker'];
                    }
                }
            });
        }

        return marker;
    };

    //remove single marker
    EE_GMAPS.removeMarker = function (mapID, marker_name) {

        var index;

        //loop over the markers
        $.each(EE_GMAPS.markers[mapID], function (key, val) {
            //search the array
            if (val['keys'] != undefined && index == undefined) {
                if (jQuery.inArray(marker_name, val['keys']) != -1) {
                    //set the index
                    index = key;
                }
            }
        });

        //remove marker
        if (index != undefined) {
            EE_GMAPS.markers[mapID].gmaps_remove(index);
        }

        //remove latlng from array
        $.each(EE_GMAPS.latlngs, function (k, v) {
            if (k == index) {
                EE_GMAPS.latlngs.gmaps_remove(k);
                //delete EE_GMAPS.latlngs[k];
            }
        });

        //update markerNumber
        $.each(EE_GMAPS.markers[mapID], function (key, val) {
            val['marker'].markerNumber = key;
            val['keys'][0] = key;
        });
    };

    //update the marker cache with the new markers
    //new_order is an array with the key/index as new number, and the value the uuid
    EE_GMAPS.updateMarkerCache = function (mapID, new_order) {
        if ($.isArray(new_order)) {
            var new_cache = [];
            $.each(new_order, function (k, v) {
                var marker = EE_GMAPS.searchMarker(mapID, v);
                if (marker != undefined) {
                    var old_markerNumber = marker.markerNumber
                    //set the new marker
                    marker.markerNumber = k;
                    EE_GMAPS.markers[mapID][old_markerNumber].keys[0] = k;
                    //save to new cache
                    new_cache.push(EE_GMAPS.markers[mapID][old_markerNumber]);
                }
            });

            //set the new cache
            EE_GMAPS.markers[mapID] = new_cache;
        }
    };

    //save polyline or polygon
    EE_GMAPS.saveArtOverlay = function (mapID, object, type) {

        //set the type array, always with s like polylines or circles
        var type_array = type + 's';

        if (type != undefined && EE_GMAPS[type_array] != undefined && object != undefined) {
            //set the map array
            if (EE_GMAPS[type_array][mapID] == undefined) {
                EE_GMAPS[type_array][mapID] = [];
            }
            //get the index
            var index = EE_GMAPS[type_array][mapID].length;
            //set markerNumber
            object.objectNumber = index;
            //set the uuuid
            object.objectUUID = createUUID();
            //set the arrays
            EE_GMAPS[type_array][mapID][index] = [];
            EE_GMAPS[type_array][mapID][index]['object'] = object;
            EE_GMAPS[type_array][mapID][index]['keys'] = [index, object.objectUUID];

            //return number
            return object.objectNumber;
        }
    };

    //set the poly to the arrays
    EE_GMAPS.searchArtOverlay = function (mapID, object_name, type) {

        //set the type array, always with s like polylines or circles
        var type_array = type + 's';

        if (type != undefined && EE_GMAPS[type_array] != undefined) {
            var object;

            //loop over the markers
            $.each(EE_GMAPS[type_array][mapID], function (key, val) {
                //search the array
                if (val['keys'] != undefined) {
                    if (jQuery.inArray(object_name, val['keys']) != -1) {
                        object = EE_GMAPS[type_array][mapID][key]['object'];
                    }
                }
            });

            //return
            return object;
        }
    };

    //remove single poly
    EE_GMAPS.removeArtOverlay = function (mapID, object_name, type) {

        //set the type array, always with s like polylines or circles
        var type_array = type + 's';
        var index;

        if (type != undefined && EE_GMAPS[type_array] != undefined) {
            //loop over the markers
            $.each(EE_GMAPS[type_array][mapID], function (key, val) {
                //search the array
                if (val['keys'] != undefined && typeof (index) == 'undefined') {
                    if (jQuery.inArray(object_name, val['keys']) != -1) {
                        //set the index
                        index = key;
                    }
                }
            });

            //remove marker
            if (index != undefined) {
                EE_GMAPS[type_array][mapID].gmaps_remove(index);
            }

            //update markerNumber
            $.each(EE_GMAPS[type_array][mapID], function (key, val) {
                val['object'].objectNumber = key;
                val['keys'][0] = key;
            });
        }
    };

    //update the poly cache with the new polylines
    //new_order is an array with the key/index as new number, and the value the uuid
    EE_GMAPS.updateArtOverlayCache = function (mapID, new_order, type) {

        //set the type array, always with s like polylines or circles
        var type_array = type + 's';

        if (type != undefined && EE_GMAPS[type_array] != undefined) {
            if ($.isArray(new_order)) {
                var new_cache = [];
                $.each(new_order, function (k, v) {
                    var object = EE_GMAPS.searchArtOverlay(mapID, v, type);
                    if (object != undefined) {
                        var old_objectNumber = object.objectNumber
                        //set the new poly
                        object.objectNumber = k;
                        EE_GMAPS[type_array][mapID][old_objectNumber].keys[0] = k;
                        //save to new cache
                        new_cache.push(EE_GMAPS[type_array][mapID][old_objectNumber]);
                    }
                });
                //set the new cache
                EE_GMAPS[type_array][mapID] = new_cache;
            }
        }
    };

    //get the map
    EE_GMAPS.getMap = function (id) {
        if (EE_GMAPS._map_['#' + id] != undefined) {
            return EE_GMAPS._map_['#' + id];
        }
        return false;
    };

    //get the map
    EE_GMAPS.fitMap = function (key) {
        if (EE_GMAPS.markers[key] != undefined) {
            //console.log(EE_GMAPS.markers[key]);
            //EE_GMAPS['#'_key].fitLatLngBounds(latlng);
        }
    };

    //simple Geolocation wrapper
    EE_GMAPS.geolocate = function(callback){
        GMaps.geolocate({
            success: function (position) {
                if (typeof callback === "function") {
                    // Call it, since we have confirmed it is callable​
                    callback(position);
                }
            },
            error: function (error) {
                console.log('Geolocation failed: ' + error.message);
            },
            not_supported: function () {
                console.log("Your browser does not support geolocation");
            }
        });
    };




    //----------------------------------------------------------------------------------------------------------//
    // Public functions //
    //----------------------------------------------------------------------------------------------------------//

    ///create an onclick event wrapper for an marker
    EE_GMAPS.api = EE_GMAPS.triggerEvent = function (type, options) { 
        //no type
        if (type == '') {
            return false;
        }

        //options 
        options = $.extend({
            mapID: '',
            key: ''
        }, options);

        //set the vars
        var mapID, map, latlng = [];
        var marker;

        //set the mapID
        if (options.mapID != '') {
            //set the mapID
            mapID = options.mapID;
            delete options.mapID;

            //get the map
            map = EE_GMAPS.getMap(mapID);
        }

        //what do we do
        switch (type) {
            //marker click
        case 'markerClick':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);

            //trigger the click
            if (marker != undefined) {
                google.maps.event.trigger(marker, 'click');
                //is there a map
                if (map) {
                    map.setCenter(marker.position.lat(), marker.position.lng());
                }
            }
            break;

        //callback for the marker click (added 2.14)
        case 'markerClickCallback':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);

            //trigger the click
            if (marker != undefined && typeof options.callback == 'function') {
                google.maps.event.addListener(marker, "click", function () {
                    //assign marker and map object
                    options.marker = marker;
                    options.map = map;
                    //call the callback
                    options.callback(map);
                });
            }
            break;

            //close infowindow
        case 'infowindowClose':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);
            //close infoWindow
            if (marker != undefined) {
                marker.infoWindow.close();
            }
            break;

        //Get the marker array
        case 'getAllMarkers':
            return (EE_GMAPS.markers);
        break;

            //Get the marker array
        case 'getMarkers':
            return (EE_GMAPS.markers[mapID]);
            break;

            //close infowindow
        case 'refresh':
            //is there a map
            if (map) {
                map.refresh();
            }
            break;

            //refresh all maps
        case 'refreshAll':
            var maps = _.values(EE_GMAPS._map_);
            _.each(maps, function(v){
                v.refresh();

                //fitzoom
                if(typeof options.center == 'boolean' && options.center === true) {
                    v.fitZoom();
                }
            });
            break;

        // set Zoom level
        case 'setZoom':
            //is there a map
            if (map) {
                map.setZoom(options.zoomLevel);
            }
            break;

        // set Zoom level
        case 'fitZoom':
            //is there a map
            if (map) {
                map.fitZoom();

                //set the zoom manually
                if (options.zoomLevel != undefined) {
                    map.setZoom(options.zoomLevel);
                }
            }
            break;

        // set Zoom level (added 2.9)
        case 'center':
            //is there a map
            if (map) {
                map.setCenter(options.lat, options.lng);
            }
            break;

            // add Marker, and return the marker numbers
        case 'addMarker':
            //is there a map
            if (map) {
                var ids = [];
                //multiple
                if (options.multi != undefined && _.isArray(options.multi)) {
                    ids = [];
                    $.each(options.multi, function (k, v) {
                        var new_marker = map.addMarker(v);
                        ids.push(EE_GMAPS.saveMarker(mapID, new_marker));

                        //callback
                        if ((k + 1) == options.multi.length) {

                            //fit map
                            if (options.fitTheMap) {
                                map.fitZoom();
                            }

                            //callback
                            if (options.callback && typeof (options.callback) == 'function') {
                                setTimeout(function () {
                                    options.callback()
                                }, 200);
                            }
                        }
                    });

                    //single marker
                } else {
                    var new_marker = map.addMarker(options);
                    ids = EE_GMAPS.saveMarker(mapID, new_marker);

                    //fit map
                    if (options.fitTheMap) {
                        map.fitZoom();
                    }

                    //callback
                    if (options.callback && typeof (options.callback) == 'function') {
                        setTimeout(function () {
                            options.callback()
                        }, 200);
                    }
                }

                return ids;
            }
            break;

            //remove marker
        case 'removeMarker':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);
            //is there a map
            if (map && marker != undefined) {
                //remove from the gmaps.js
                map.removeMarker(marker);
                //remove from the cache
                EE_GMAPS.removeMarker(mapID, options.key);
            }
            break;

            // hide existing Marker
        case 'hideMarker':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);
            //is there a map
            if (map && marker != undefined) {
                //remove from map
                marker.setVisible(false);
            }
            break;

            // show existing Marker
        case 'showMarker':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);
            //is there a map
            if (map && marker != undefined) {
                //remove from map
                marker.setVisible(true);
            }
            break;

            // show existing Marker (added 2.9)
        case 'updateMarker':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);
            //is there a map
            if (map && marker != undefined) {
                //remove key
                delete options.key;
                //set infowindow if needed
                if (options.infoWindow != undefined && options.infoWindow.content != undefined) {
                    if (marker.infoWindow != undefined) {
                        marker.infoWindow.setContent(options.infoWindow.content);
                    }
                    delete options.infoWindow;
                }
                //set the new options
                marker.setOptions(options);
                //refresh the map
                map.refresh();
            }
            break;

            // Get the marker
        case 'getMarker':
            //get the marker
            marker = EE_GMAPS.searchMarker(mapID, options.key);
            //is there a map
            if (map && marker != undefined) {
                //remove from map
                return marker;
            }
            break;

            // Remove all markers
        case 'removeMarkers':
            //is there a map
            if (map) {
                map.removeMarkers();
                //reset marker cache
                EE_GMAPS.markers = [];
            }
            break;

            // Hide all markers (added 2.12.9))
        case 'hideMarkers':
            //is there a map
            if (map && EE_GMAPS.markers[mapID] != undefined) {
                $.each(EE_GMAPS.markers[mapID], function (k, v) {
                    //remove from map
                    v.marker.setVisible(false);
                });
            }
            break;

            // Show all markers (added 2.12.9)
        case 'showMarkers':
            //is there a map
            if (map && EE_GMAPS.markers[mapID] != undefined) {
                $.each(EE_GMAPS.markers[mapID], function (k, v) {
                    //remove from map
                    v.marker.setVisible(true);
                });
            }
            break;

            // create the context menu (added 2.9)
        case 'contextMenu':
            //is there a map
            if (map) {
                map.setContextMenu(options);
            }
            break;

            // Get the map object (added 2.9)
        case 'getMap':
            //is there a map
            if (map) {
                return map;
            }
            break;

            // Add a layer
        case 'addLayer':
            //is there a map
            if (map) {
                if (options.layerName != undefined) {
                    map.addLayer(options.layerName);
                }
            }
            break;

            // Remove a layer
        case 'removeLayer':
            //is there a map
            if (map) {
                if (options.layerName != undefined) {
                    map.removeLayer(options.layerName);
                }
            }
            break;

            // Create a circle
        case 'addCircle':
            //is there a map
            if (map) {
                //multiple
                if (options.multi != undefined && _.isArray(options.multi)) {
                    var ids = [];
                    $.each(options.multi, function (k, v) {
                        var new_circle = map.drawCircle(v);
                        ids.push(EE_GMAPS.saveArtOverlay(mapID, new_circle, 'circle'));
                    });
                    return ids;

                    //single polygon
                } else {
                    var new_circle = map.drawCircle(options);
                    return EE_GMAPS.saveArtOverlay(mapID, new_circle, 'circle');
                }
            }
            break;

            // Get the polygon (added 2.11.1)
        case 'getCircle':
            //get the marker
            circle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'circle');
            //is there a map
            if (map && circle != undefined) {
                //remove from map
                return circle;
            }
            break;

            // update a polygon (added 2.11.1)
        case 'updateCircle':
            //get the marker
            circle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'circle');
            //is there a map
            if (map && circle != undefined) {
                //remove key
                delete options.key;
                //set the new options
                circle.setOptions(options);
                //refresh the map
                map.refresh();
            }
            break;

            //remove polygon (added 2.11.1)
        case 'removeCircle':
            //get the marker
            circle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'circle');
            //is there a map
            if (map && circle != undefined) {
                //remove from gmaps.js
                circle.setMap(null);
                //remove from the cache
                EE_GMAPS.removeArtOverlay(mapID, options.key, 'circle');
            }
            break;

            // Create a circle
        case 'addRectangle':
            //is there a map
            if (map) {
                //multiple
                if (options.multi != undefined && _.isArray(options.multi)) {
                    var ids = [];
                    $.each(options.multi, function (k, v) {
                        var new_rectangle = map.drawRectangle(v);
                        ids.push(EE_GMAPS.saveArtOverlay(mapID, new_rectangle, 'rectangle'));
                    });
                    return ids;

                    //single polygon
                } else {
                    var new_rectangle = map.drawRectangle(options);
                    return EE_GMAPS.saveArtOverlay(mapID, new_rectangle, 'rectangle');
                }
            }
            break;

            // Get the polygon (added 2.11.1)
        case 'getRectangle':
            //get the marker
            rectangle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'rectangle');
            //is there a map
            if (map && rectangle != undefined) {
                //remove from map
                return rectangle;
            }
            break;

            // update a polygon (added 2.11.1)
        case 'updateRectangle':
            //get the marker
            rectangle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'rectangle');
            //is there a map
            if (map && rectangle != undefined) {
                //remove key
                delete options.key;
                //set the new options
                rectangle.setOptions(options);
                //refresh the map
                map.refresh();
            }
            break;

            //remove polygon (added 2.11.1)
        case 'removeRectangle':
            //get the marker
            rectangle = EE_GMAPS.searchArtOverlay(mapID, options.key, 'rectangle');
            //is there a map
            if (map && rectangle != undefined) {
                //remove from gmaps.js
                rectangle.setMap(null);
                //remove from the cache
                EE_GMAPS.removeArtOverlay(mapID, options.key, 'rectangle');
            }
            break;

            // Create a Polygon
        case 'addPolygon':
            //is there a map
            if (map) {
                //multiple
                if (options.multi != undefined && _.isArray(options.multi)) {
                    var ids = [];
                    $.each(options.multi, function (k, v) {
                        var new_polygon = map.drawPolygon(v);
                        ids.push(EE_GMAPS.saveArtOverlay(mapID, new_polygon, 'polygon'));
                    });
                    return ids;

                    //single polygon
                } else {
                    var new_polygon = map.drawPolygon(options);
                    return EE_GMAPS.saveArtOverlay(mapID, new_polygon, 'polygon');
                }
            }
            break;

            // Get the polygon (added 2.11.1)
        case 'getPolygon':
            //get the marker
            polygon = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polygon');
            //is there a map
            if (map && polygon != undefined) {
                //remove from map
                return polygon;
            }
            break;

            // update a polygon (added 2.11.1)
        case 'updatePolygon':
            //get the marker
            polygon = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polygon');
            //is there a map
            if (map && polygon != undefined) {
                //remove key
                delete options.key;
                //set the new options
                polygon.setOptions(options);
                //refresh the map
                map.refresh();
            }
            break;

            //remove polygon (added 2.11.1)
        case 'removePolygon':
            //get the marker
            polygon = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polygon');
            //is there a map
            if (map && polygon != undefined) {
                //remove from gmaps.js
                map.removePolygon(polygon);
                //remove from the cache
                EE_GMAPS.removeArtOverlay(mapID, options.key, 'polygon');
            }
            break;

            // Create a Polyline
        case 'addPolyline':
            //is there a map
            if (map) {
                //multiple
                if (options.multi != undefined && _.isArray(options.multi)) {
                    var ids = [];
                    $.each(options.multi, function (k, v) {
                        var new_polyline = map.drawPolyline(v);
                        ids.push(EE_GMAPS.saveArtOverlay(mapID, new_polyline, 'polyline'));
                    });
                    return ids;

                    //single polyline
                } else {
                    var new_polyline = map.drawPolyline(options);
                    return EE_GMAPS.saveArtOverlay(mapID, new_polyline, 'polyline');
                }
            }
            break;

            // Get the polyline (added 2.11.1)
        case 'getPolyline':
            //get the marker
            polyline = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polyline');
            //is there a map
            if (map && polyline != undefined) {
                //remove from map
                return polyline;
            }
            break;

            // Get the polyline (added 2.11.1)
        case 'updatePolyline':
            //get the marker
            polyline = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polyline');
            //is there a map
            if (map && polyline != undefined) {
                //remove key
                delete options.key;
                //set the new options
                polyline.setOptions(options);
                //refresh the map
                map.refresh();
            }
            break;

            //remove marker (added 2.11.1)
        case 'removePolyline':
            //get the marker
            polyline = EE_GMAPS.searchArtOverlay(mapID, options.key, 'polyline');
            //is there a map
            if (map && polyline != undefined) {
                //remove from gmaps.js
                map.removePolyline(polyline);
                //remove from the cache
                EE_GMAPS.removeArtOverlay(mapID, options.key, 'polyline');
            }
            break;

            // Update a map with new settings
        case 'updateMap':
            //is there a map
            if (map) {
                if (options.setMapTypeId != undefined) {
                    map.setMapTypeId(google.maps.MapTypeId[options.setMapTypeId.toUpperCase()]);
                    delete options.setMapTypeId;
                }
                map.setOptions(options);
            }
            break;

            // Update a map with new settings
        case 'fitMap':
            //is there a map
            if (map) {
                //EE_GMAPS.fitMap(key);
            }
            break;

            //add the google map like overlay (added 3.0)
        case 'addGoogleOverlay':

            //is there a map
            if (map) {
                var new_options = {
                    overlay_html : options.html || '',
                    selector : '#'+mapID.replace('#', ''),
                    overlay_position : options.position || 'left'  
                };
               
                EE_GMAPS.addGoogleOverlay(map, new_options, true);
            }
        break;

         //add the google map like overlay (added 3.0)
        case 'updateGoogleOverlay':

            //is there a map
            if (map) {
                var new_options = {
                    overlay_html : options.html || '',
                    selector : '#'+mapID.replace('#', ''),
                    overlay_position : options.position || 'left'  
                };
               
                EE_GMAPS.updateGoogleOverlay(map, new_options, true);
            }
        break;

           //add the google map like overlay (added 3.0)
        case 'removeGoogleOverlay':

            //is there a map
            if (map) {
                EE_GMAPS.removeGoogleOverlay('#'+mapID.replace('#', ''));
            }
        break;

        case 'geolocation' :
            GMaps.geolocate({
                success: function (position) {
                    if (typeof options.callback === "function") {
                        // Call it, since we have confirmed it is callable​
                        options.callback(position);
                    }
                },
                error: function (error) {
                    console.log('Geolocation failed: ' + error.message);
                },
                not_supported: function () {
                    console.log("Your browser does not support geolocation");
                }
            });
        break;

            // Geocode using the API way to cache all addresses
        case 'geocode':

            var sessionKey = createUUID();

            //latlng reverse geocoding
            if (options.latlng != undefined) {
                $.post(EE_GMAPS.api_path+'&type=latlng', {
                    input: options.latlng
                }, function (result) {
                    if (options.callback && typeof (options.callback) === "function") {
                        options.callback(result, 'latlng', sessionKey);
                    }
                });
            }

            //address geocoding
            if (options.address != undefined) {
                $.post(EE_GMAPS.api_path+'&type=address', {
                    input: options.address
                }, function (result) {
                    if (options.callback && typeof (options.callback) === "function") {
                        options.callback(result, 'address', sessionKey);
                    }
                });
            }

            //ip geocoding
            if (options.ip != undefined) {
                $.post(EE_GMAPS.api_path+'&type=ip', {
                    input: options.ip
                }, function (result) {
                    if (options.callback && typeof (options.callback) === "function") {
                        options.callback(result, 'ip', sessionKey);
                    }
                });
            }
            break;
        }
    };

    //create a show trigger 
    $.each(["show", "toggle", "toggleClass", "addClass", "removeClass"], function () {
        var _oldFn = $.fn[this];
        $.fn[this] = function () {
            var hidden = this.find(":hidden").add(this.filter(":hidden"));
            var result = _oldFn.apply(this, arguments);
            hidden.filter(":visible").each(function () {
                $(this).triggerHandler("show"); //No bubbling
            });
            return result;
        };
    });

}(jQuery));
/*
 * Plugins.js
 * http://reinos.nl
 * 
 * Plugin file for the Gmaps module
 *
 * @package            Gmaps for EE2
 * @author             Rein de Vries (info@reinos.nl)
 * @copyright          Copyright (c) 2013 Rein de Vries
 * @license            http://reinos.nl/commercial-license
 * @link               http://reinos.nl/add-ons/gmaps
 */

//Object.keys fucntion for IE8 and older
Object.keys = Object.keys || (function () {
    var hasOwnProperty = Object.prototype.hasOwnProperty,
        hasDontEnumBug = !{toString:null}.propertyIsEnumerable("toString"),
        DontEnums = [
            'toString',
            'toLocaleString',
            'valueOf',
            'hasOwnProperty',
            'isPrototypeOf',
            'propertyIsEnumerable',
            'constructor'
        ],
        DontEnumsLength = DontEnums.length;
 
    return function (o) {
        if (typeof o != "object" && typeof o != "function" || o === null)
            throw new TypeError("Object.keys called on a non-object");
 
        var result = [];
        for (var name in o) {
            if (hasOwnProperty.call(o, name))
                result.push(name);
        }
 
        if (hasDontEnumBug) {
            for (var i = 0; i < DontEnumsLength; i++) {
                if (hasOwnProperty.call(o, DontEnums[i]))
                    result.push(DontEnums[i]);
            }
        }
 
        return result;
    };
})();

//add getBounds on the plyline
if (!google.maps.Polyline.prototype.getBounds) {
   google.maps.Polyline.prototype.getBounds = function(latLng) {
      var bounds = new google.maps.LatLngBounds();
      var path = this.getPath();
      for (var i = 0; i < path.getLength(); i++) {
         bounds.extend(path.getAt(i));
      }
      return bounds;
   }
}

function createUUID() {
    // http://www.ietf.org/rfc/rfc4122.txt
    var s = [];
    var hexDigits = "0123456789abcdef";
    for (var i = 0; i < 36; i++) {
        s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
    }
    s[14] = "4";  // bits 12-15 of the time_hi_and_version field to 0010
    s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);  // bits 6-7 of the clock_seq_hi_and_reserved to 01
    s[8] = s[13] = s[18] = s[23] = "-";

    var uuid = s.join("");
    return uuid;
}

function base64_decode (data) {
  // http://kevin.vanzonneveld.net
  // +   original by: Tyler Akins (http://rumkin.com)
  // +   improved by: Thunder.m
  // +      input by: Aman Gupta
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Onno Marsman
  // +   bugfixed by: Pellentesque Malesuada
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +      input by: Brett Zamir (http://brett-zamir.me)
  // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // *     example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
  // *     returns 1: 'Kevin van Zonneveld'
  // mozilla has this native
  // - but breaks in 2.0.0.12!
  //if (typeof this.window['atob'] == 'function') {
  //    return atob(data);
  //}
  var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
  var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
    ac = 0,
    dec = "",
    tmp_arr = [];

  if (!data) {
    return data;
  }

  data += '';

  do { // unpack four hexets into three octets using index points in b64
    h1 = b64.indexOf(data.charAt(i++));
    h2 = b64.indexOf(data.charAt(i++));
    h3 = b64.indexOf(data.charAt(i++));
    h4 = b64.indexOf(data.charAt(i++));

    bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

    o1 = bits >> 16 & 0xff;
    o2 = bits >> 8 & 0xff;
    o3 = bits & 0xff;

    if (h3 == 64) {
      tmp_arr[ac++] = String.fromCharCode(o1);
    } else if (h4 == 64) {
      tmp_arr[ac++] = String.fromCharCode(o1, o2);
    } else {
      tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
    }
  } while (i < data.length);

  dec = tmp_arr.join('');

  return dec;
}

function base64_encode (data) {
  // http://kevin.vanzonneveld.net
  // +   original by: Tyler Akins (http://rumkin.com)
  // +   improved by: Bayron Guevara
  // +   improved by: Thunder.m
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Pellentesque Malesuada
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: Rafał Kukawski (http://kukawski.pl)
  // *     example 1: base64_encode('Kevin van Zonneveld');
  // *     returns 1: 'S2V2aW4gdmFuIFpvbm5ldmVsZA=='
  // mozilla has this native
  // - but breaks in 2.0.0.12!
  //if (typeof this.window['btoa'] == 'function') {
  //    return btoa(data);
  //}
  var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
  var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
    ac = 0,
    enc = "",
    tmp_arr = [];

  if (!data) {
    return data;
  }

  do { // pack three octets into four hexets
    o1 = data.charCodeAt(i++);
    o2 = data.charCodeAt(i++);
    o3 = data.charCodeAt(i++);

    bits = o1 << 16 | o2 << 8 | o3;

    h1 = bits >> 18 & 0x3f;
    h2 = bits >> 12 & 0x3f;
    h3 = bits >> 6 & 0x3f;
    h4 = bits & 0x3f;

    // use hexets to index into b64, and append result to encoded string
    tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
  } while (i < data.length);

  enc = tmp_arr.join('');

  var r = data.length % 3;

  return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);

}

/**
 * ReplaceAll by Fagner Brack (MIT Licensed)
 * Replaces all occurrences of a substring in a string
 */
if (!String.prototype.gmaps_replaceAll) {
    String.prototype.gmaps_replaceAll = function( token, newToken, ignoreCase ) {
        var _token;
        var str = this + "";
        var i = -1;

        if ( typeof token === "string" ) {

            if ( ignoreCase ) {

                _token = token.toLowerCase();

                while( (
                    i = str.toLowerCase().indexOf(
                        token, i >= 0 ? i + newToken.length : 0
                    ) ) !== -1
                ) {
                    str = str.substring( 0, i ) +
                        newToken +
                        str.substring( i + token.length );
                }

            } else {
                return this.split( token ).join( newToken );
            }

        }
    return str;
    };
}

// Array Remove - By John Resig (MIT Licensed)
if (!Array.prototype.gmaps_remove) {
    Array.prototype.gmaps_remove = function(from, to) {
        if(typeof this.slice !== 'undefined') {
        	var rest = this.slice((to || from) + 1 || this.length);
        	this.length = from < 0 ? this.length + from : from;
        	return this.push.apply(this, rest);
        }
    };
};
/*
    json2.js
    2012-10-08

    Public Domain.

    NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.

    See http://www.JSON.org/js.html


    This code should be minified before deployment.
    See http://javascript.crockford.com/jsmin.html

    USE YOUR OWN COPY. IT IS EXTREMELY UNWISE TO LOAD CODE FROM SERVERS YOU DO
    NOT CONTROL.


    This file creates a global JSON object containing two methods: stringify
    and parse.

        JSON.stringify(value, replacer, space)
            value       any JavaScript value, usually an object or array.

            replacer    an optional parameter that determines how object
                        values are stringified for objects. It can be a
                        function or an array of strings.

            space       an optional parameter that specifies the indentation
                        of nested structures. If it is omitted, the text will
                        be packed without extra whitespace. If it is a number,
                        it will specify the number of spaces to indent at each
                        level. If it is a string (such as '\t' or '&nbsp;'),
                        it contains the characters used to indent at each level.

            This method produces a JSON text from a JavaScript value.

            When an object value is found, if the object contains a toJSON
            method, its toJSON method will be called and the result will be
            stringified. A toJSON method does not serialize: it returns the
            value represented by the name/value pair that should be serialized,
            or undefined if nothing should be serialized. The toJSON method
            will be passed the key associated with the value, and this will be
            bound to the value

            For example, this would serialize Dates as ISO strings.

                Date.prototype.toJSON = function (key) {
                    function f(n) {
                        // Format integers to have at least two digits.
                        return n < 10 ? '0' + n : n;
                    }

                    return this.getUTCFullYear()   + '-' +
                         f(this.getUTCMonth() + 1) + '-' +
                         f(this.getUTCDate())      + 'T' +
                         f(this.getUTCHours())     + ':' +
                         f(this.getUTCMinutes())   + ':' +
                         f(this.getUTCSeconds())   + 'Z';
                };

            You can provide an optional replacer method. It will be passed the
            key and value of each member, with this bound to the containing
            object. The value that is returned from your method will be
            serialized. If your method returns undefined, then the member will
            be excluded from the serialization.

            If the replacer parameter is an array of strings, then it will be
            used to select the members to be serialized. It filters the results
            such that only members with keys listed in the replacer array are
            stringified.

            Values that do not have JSON representations, such as undefined or
            functions, will not be serialized. Such values in objects will be
            dropped; in arrays they will be replaced with null. You can use
            a replacer function to replace those with JSON values.
            JSON.stringify(undefined) returns undefined.

            The optional space parameter produces a stringification of the
            value that is filled with line breaks and indentation to make it
            easier to read.

            If the space parameter is a non-empty string, then that string will
            be used for indentation. If the space parameter is a number, then
            the indentation will be that many spaces.

            Example:

            text = JSON.stringify(['e', {pluribus: 'unum'}]);
            // text is '["e",{"pluribus":"unum"}]'


            text = JSON.stringify(['e', {pluribus: 'unum'}], null, '\t');
            // text is '[\n\t"e",\n\t{\n\t\t"pluribus": "unum"\n\t}\n]'

            text = JSON.stringify([new Date()], function (key, value) {
                return this[key] instanceof Date ?
                    'Date(' + this[key] + ')' : value;
            });
            // text is '["Date(---current time---)"]'


        JSON.parse(text, reviver)
            This method parses a JSON text to produce an object or array.
            It can throw a SyntaxError exception.

            The optional reviver parameter is a function that can filter and
            transform the results. It receives each of the keys and values,
            and its return value is used instead of the original value.
            If it returns what it received, then the structure is not modified.
            If it returns undefined then the member is deleted.

            Example:

            // Parse the text. Values that look like ISO date strings will
            // be converted to Date objects.

            myData = JSON.parse(text, function (key, value) {
                var a;
                if (typeof value === 'string') {
                    a =
/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}(?:\.\d*)?)Z$/.exec(value);
                    if (a) {
                        return new Date(Date.UTC(+a[1], +a[2] - 1, +a[3], +a[4],
                            +a[5], +a[6]));
                    }
                }
                return value;
            });

            myData = JSON.parse('["Date(09/09/2001)"]', function (key, value) {
                var d;
                if (typeof value === 'string' &&
                        value.slice(0, 5) === 'Date(' &&
                        value.slice(-1) === ')') {
                    d = new Date(value.slice(5, -1));
                    if (d) {
                        return d;
                    }
                }
                return value;
            });


    This is a reference implementation. You are free to copy, modify, or
    redistribute.
*/

/*jslint evil: true, regexp: true */

/*members "", "\b", "\t", "\n", "\f", "\r", "\"", JSON, "\\", apply,
    call, charCodeAt, getUTCDate, getUTCFullYear, getUTCHours,
    getUTCMinutes, getUTCMonth, getUTCSeconds, hasOwnProperty, join,
    lastIndex, length, parse, prototype, push, replace, slice, stringify,
    test, toJSON, toString, valueOf
*/


// Create a JSON object only if one does not already exist. We create the
// methods in a closure to avoid creating global variables.

if (typeof JSON !== 'object') {
    JSON = {};
}

(function () {
    'use strict';

    function f(n) {
        // Format integers to have at least two digits.
        return n < 10 ? '0' + n : n;
    }

    if (typeof Date.prototype.toJSON !== 'function') {

        Date.prototype.toJSON = function (key) {

            return isFinite(this.valueOf())
                ? this.getUTCFullYear()     + '-' +
                    f(this.getUTCMonth() + 1) + '-' +
                    f(this.getUTCDate())      + 'T' +
                    f(this.getUTCHours())     + ':' +
                    f(this.getUTCMinutes())   + ':' +
                    f(this.getUTCSeconds())   + 'Z'
                : null;
        };

        String.prototype.toJSON      =
            Number.prototype.toJSON  =
            Boolean.prototype.toJSON = function (key) {
                return this.valueOf();
            };
    }

    var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
        escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
        gap,
        indent,
        meta = {    // table of character substitutions
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        },
        rep;


    function quote(string) {

// If the string contains no control characters, no quote characters, and no
// backslash characters, then we can safely slap some quotes around it.
// Otherwise we must also replace the offending characters with safe escape
// sequences.

        escapable.lastIndex = 0;
        return escapable.test(string) ? '"' + string.replace(escapable, function (a) {
            var c = meta[a];
            return typeof c === 'string'
                ? c
                : '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
        }) + '"' : '"' + string + '"';
    }


    function str(key, holder) {

// Produce a string from holder[key].

        var i,          // The loop counter.
            k,          // The member key.
            v,          // The member value.
            length,
            mind = gap,
            partial,
            value = holder[key];

// If the value has a toJSON method, call it to obtain a replacement value.

        if (value && typeof value === 'object' &&
                typeof value.toJSON === 'function') {
            value = value.toJSON(key);
        }

// If we were called with a replacer function, then call the replacer to
// obtain a replacement value.

        if (typeof rep === 'function') {
            value = rep.call(holder, key, value);
        }

// What happens next depends on the value's type.

        switch (typeof value) {
        case 'string':
            return quote(value);

        case 'number':

// JSON numbers must be finite. Encode non-finite numbers as null.

            return isFinite(value) ? String(value) : 'null';

        case 'boolean':
        case 'null':

// If the value is a boolean or null, convert it to a string. Note:
// typeof null does not produce 'null'. The case is included here in
// the remote chance that this gets fixed someday.

            return String(value);

// If the type is 'object', we might be dealing with an object or an array or
// null.

        case 'object':

// Due to a specification blunder in ECMAScript, typeof null is 'object',
// so watch out for that case.

            if (!value) {
                return 'null';
            }

// Make an array to hold the partial results of stringifying this object value.

            gap += indent;
            partial = [];

// Is the value an array?

            if (Object.prototype.toString.apply(value) === '[object Array]') {

// The value is an array. Stringify every element. Use null as a placeholder
// for non-JSON values.

                length = value.length;
                for (i = 0; i < length; i += 1) {
                    partial[i] = str(i, value) || 'null';
                }

// Join all of the elements together, separated with commas, and wrap them in
// brackets.

                v = partial.length === 0
                    ? '[]'
                    : gap
                    ? '[\n' + gap + partial.join(',\n' + gap) + '\n' + mind + ']'
                    : '[' + partial.join(',') + ']';
                gap = mind;
                return v;
            }

// If the replacer is an array, use it to select the members to be stringified.

            if (rep && typeof rep === 'object') {
                length = rep.length;
                for (i = 0; i < length; i += 1) {
                    if (typeof rep[i] === 'string') {
                        k = rep[i];
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            } else {

// Otherwise, iterate through all of the keys in the object.

                for (k in value) {
                    if (Object.prototype.hasOwnProperty.call(value, k)) {
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            }

// Join all of the member texts together, separated with commas,
// and wrap them in braces.

            v = partial.length === 0
                ? '{}'
                : gap
                ? '{\n' + gap + partial.join(',\n' + gap) + '\n' + mind + '}'
                : '{' + partial.join(',') + '}';
            gap = mind;
            return v;
        }
    }

// If the JSON object does not yet have a stringify method, give it one.

    if (typeof JSON.stringify !== 'function') {
        JSON.stringify = function (value, replacer, space) {

// The stringify method takes a value and an optional replacer, and an optional
// space parameter, and returns a JSON text. The replacer can be a function
// that can replace values, or an array of strings that will select the keys.
// A default replacer method can be provided. Use of the space parameter can
// produce text that is more easily readable.

            var i;
            gap = '';
            indent = '';

// If the space parameter is a number, make an indent string containing that
// many spaces.

            if (typeof space === 'number') {
                for (i = 0; i < space; i += 1) {
                    indent += ' ';
                }

// If the space parameter is a string, it will be used as the indent string.

            } else if (typeof space === 'string') {
                indent = space;
            }

// If there is a replacer, it must be a function or an array.
// Otherwise, throw an error.

            rep = replacer;
            if (replacer && typeof replacer !== 'function' &&
                    (typeof replacer !== 'object' ||
                    typeof replacer.length !== 'number')) {
                throw new Error('JSON.stringify');
            }

// Make a fake root object containing our value under the key of ''.
// Return the result of stringifying the value.

            return str('', {'': value});
        };
    }


// If the JSON object does not yet have a parse method, give it one.

    if (typeof JSON.parse !== 'function') {
        JSON.parse = function (text, reviver) {

// The parse method takes a text and an optional reviver function, and returns
// a JavaScript value if the text is a valid JSON text.

            var j;

            function walk(holder, key) {

// The walk method is used to recursively walk the resulting structure so
// that modifications can be made.

                var k, v, value = holder[key];
                if (value && typeof value === 'object') {
                    for (k in value) {
                        if (Object.prototype.hasOwnProperty.call(value, k)) {
                            v = walk(value, k);
                            if (v !== undefined) {
                                value[k] = v;
                            } else {
                                delete value[k];
                            }
                        }
                    }
                }
                return reviver.call(holder, key, value);
            }


// Parsing happens in four stages. In the first stage, we replace certain
// Unicode characters with escape sequences. JavaScript handles many characters
// incorrectly, either silently deleting them, or treating them as line endings.

            text = String(text);
            cx.lastIndex = 0;
            if (cx.test(text)) {
                text = text.replace(cx, function (a) {
                    return '\\u' +
                        ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
                });
            }

// In the second stage, we run the text against regular expressions that look
// for non-JSON patterns. We are especially concerned with '()' and 'new'
// because they can cause invocation, and '=' because it can cause mutation.
// But just to be safe, we want to reject all unexpected forms.

// We split the second stage into 4 regexp operations in order to work around
// crippling inefficiencies in IE's and Safari's regexp engines. First we
// replace the JSON backslash pairs with '@' (a non-JSON character). Second, we
// replace all simple value tokens with ']' characters. Third, we delete all
// open brackets that follow a colon or comma or that begin the text. Finally,
// we look to see that the remaining characters are only whitespace or ']' or
// ',' or ':' or '{' or '}'. If that is so, then the text is safe for eval.

            if (/^[\],:{}\s]*$/
                    .test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@')
                        .replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']')
                        .replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {

// In the third stage we use the eval function to compile the text into a
// JavaScript structure. The '{' operator is subject to a syntactic ambiguity
// in JavaScript: it can begin a block or an object literal. We wrap the text
// in parens to eliminate the ambiguity.

                j = eval('(' + text + ')');

// In the optional fourth stage, we recursively walk the new structure, passing
// each name/value pair to a reviver function for possible transformation.

                return typeof reviver === 'function'
                    ? walk({'': j}, '')
                    : j;
            }

// If the text is not JSON parseable, then a SyntaxError is thrown.

            throw new SyntaxError('JSON.parse');
        };
    }
}());

/*
Copyright (c) 2009, Pim Jager
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
* Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright
notice, this list of conditions and the following disclaimer in the
documentation and/or other materials provided with the distribution.
* The name Pim Jager may not be used to endorse or promote products
derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY Pim Jager ''AS IS'' AND ANY
EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Pim Jager BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
/*
    Credits for RegEx selector go to Anon:
    See his comment on: http://james.padolsey.com/javascript/extending-jquerys-selector-capabilities/
*/
(function($){
        //We use a small helper function that will return true when 'a' is undefined (so we can do if(checkUndefined(data)) return false;
        //If we would continue with undefined data we would throw error as we would be getting properties of an
        //non-exsitent object (ie typeof data === 'undefined'; data.fooBar; //throws error
        var checkUndefined = function(a) {
                return typeof a === 'undefined';
        }
        $.expr[':'].data = function(elem, counter, params){
                if(checkUndefined(elem) || checkUndefined(params)) return false;
                //:data(__) accepts 'dataKey', 'dataKey=Value', 'dataKey.InnerdataKey', 'dataKey.InnerdataKey=Value'
                //Also instead of = we accept: != (does not equal Value), ^= (starts with Value), 
                //              $= (ends with Value), *=Value (contains Value) ~=Regex returns where data matches regex
                //$(elem).data(dataKey) or $(elem).data(dataKey)[innerDataKey] (optional more innerDataKeys)
                //When no value is speciefied we return all elements that have the dataKey specified, similar to [attribute]
                var query = params[3]; //The part in the parenthesis, thus: selector:data( query )
                if(!query) return false; //query can not be anything that evaluates to false, it has to be string
                var querySplitted = query.split('='); //for dataKey=Value/dataKey.innerDataKey=Value
                //We check if the condition was an =, an !=, an $= or an *=
                var selectType = querySplitted[0].charAt( querySplitted[0].length-1 );
                if(selectType == '^' || selectType == '$' || selectType == '!' || selectType == '*' || selectType == '~'){
            //we need to remove the last char from the dataName (queryplitted[0]) because we plitted on the =
            //so the !,$,*,^ are still part of the dataname
                        querySplitted[0] = querySplitted[0].substring(0, querySplitted[0].length-1);
                }
                else selectType = '=';
                var dataName = querySplitted[0]; //dataKey or dataKey.innerDataKey
                //Now we go check if we need dataKey or dataKey.innerDataKey
                var dataNameSplitted = dataName.split('.');
                var data = $(elem).data(dataNameSplitted[0]);
                if(checkUndefined(data)) return false;
                if(dataNameSplitted[1]){//We have innerDataKeys
                        for(i=1, x=dataNameSplitted.length; i<x; i++){ //we start counting at 1 since we ignore the first value because that is the dataKey
                                data = data[dataNameSplitted[i]];
                                if(checkUndefined(data)) return false;
                        }
                }
                if(querySplitted[1]){ //should the data be of a specified value?
                        var checkAgainst = (data+'');
                                //We cast to string as the query will always be a string, otherwise boolean comparison may fail
                                //beacuse in javaScript true!='true' but (true+'')=='true'
                        //We use this switch to check if we chould check for =, $=, ^=, !=, *=
                        switch(selectType){
                                case '=': //equals
                                        return checkAgainst == querySplitted[1]; 
                                break;
                                case '!': //does not equeal
                                        return checkAgainst != querySplitted[1];
                                break;
                                case '^': //starts with
                    return checkAgainst.indexOf(querySplitted[1]) === 0;
                                break;
                                case '$': //ends with
                    return checkAgainst.substr(checkAgainst.length - querySplitted[1].length) === querySplitted[1];
                                break;
                                case '*': //contains
                    return checkAgainst.indexOf(querySplitted[1]) !== -1;
                                break;
                case '~':
                    exp = querySplitted[1].substr(1,querySplitted[1].lastIndexOf('/')-1);
                    modif = querySplitted[1].substr(querySplitted[1].lastIndexOf('/')+1);
                    re = new RegExp( exp, modif);
                    return re.test(checkAgainst);
                break;
                                default: //default should never happen
                                        return false;
                                break;
                        }                       
                }
                else{ //the data does not have to be a speciefied value
                                //, just return true (we are here so the data is specified, otherwise false would have been returned by now)
                        return true;
                }
        }
})(jQuery);

//clone array
if (!Array.prototype.gmaps_clone) {
    Array.prototype.gmaps_clone = function() { 
        if(typeof this.slice !== 'undefined') {
            return this.slice(0); 
        }
    }
}

//max function
if (!Array.prototype.gmaps_max) {
    Array.prototype.gmaps_max = function() {
      return Math.max.apply(null, this)
    }
}

//min function
if (!Array.prototype.gmaps_min) {
    Array.prototype.gmaps_min = function() {
      return Math.min.apply(null, this)
    }
}

//remove empty or null values
// Compact arrays with null entries; delete keys from objects with null value
function gmaps_remove_empty_values(obj) {
   for(var key in obj) {

      // value is empty string
      if(obj[key] === '') {
         delete obj[key];
      }

      // value is array with only emtpy strings
      if(obj[key] instanceof Array) {
         var empty = true;
         for(var i = 0; i < obj[key].length; i++) {
             if(obj[key][i] !== '') {
                empty = false;
                break;
             }
         }

         if(empty)
            delete obj[key];
      }

      // value is object with only empty strings or arrays of empty strings
      if(typeof obj[key] === "object") {
         obj[key] = gmaps_remove_empty_values(obj[key]);

         var hasKeys = false;
         for(var objKey in obj[key]) {
            hasKeys = true;
            break;
         }

         if(!hasKeys)
            delete obj[key];
      }
   }

   return obj;
}

String.prototype.gmaps_bool = function() {
    return (/^true$/i).test(this);
};