/**
 * Class for Hash Map datastructure.
 *
 * Used for storing container values and validation errors, mostly borrowed
 * from closure library.
 *
 * @param   {Object}    [inMap] Object or qf.Map instance to initialize the map
 * @constructor
 */
qf.Map = function(inMap)
{
   /**
    * Actual JS Object used to store the map
    * @type {Object}
    * @private
    */
    this._map   = {};

   /**
    * An array of map keys
    * @type {String[]}
    * @private
    */
    this._keys  = [];

   /**
    * Number of key-value pairs in the map
    * @type {number}
    * @private
    */
    this._count = 0;

    if (inMap) {
        this.merge(inMap);
    }
};

qf.Map.prototype = (function(){
    /**
     * Wrapper function for hasOwnProperty
     * @param   {Object}    obj
     * @param   {*}         key
     * @returns {boolean}
     * @private
     */
    function _hasKey(obj, key)
    {
        return Object.prototype.hasOwnProperty.call(obj, key);
    }

    /**
     * Removes keys that are no longer in the map from the _keys array
     * @private
     */
    function _cleanupKeys()
    {
        if (this._count == this._keys.length) {
            return;
        }
        var srcIndex  = 0;
        var destIndex = 0;
        var seen      = {};
        while (srcIndex < this._keys.length) {
            var key = this._keys[srcIndex];
            if (_hasKey(this._map, key)
                && !_hasKey(seen, key)
            ) {
                this._keys[destIndex++] = key;
                seen[key] = true;
            }
            srcIndex++;
        }
        this._keys.length = destIndex;
    }

    return {
        /**
         * Whether the map has the given key
         * @param   {*}     key
         * @returns {boolean}
         */
        hasKey: function(key)
        {
            return _hasKey(this._map, key);
        },

        /**
         * Returns the number of key-value pairs in the Map
         * @returns {number}
         */
        length: function()
        {
            return this._count;
        },

        /**
         * Returns the values of the Map
         * @returns {Array}
         */
        getValues: function()
        {
            _cleanupKeys.call(this);

            var ret = [];
            for (var i = 0; i < this._keys.length; i++) {
                ret.push(this._map[this._keys[i]]);
            }
            return ret;
        },

        /**
         * Returns the keys of the Map
         * @returns {String[]}
         */
        getKeys: function()
        {
            _cleanupKeys.call(this);
            return (this._keys.concat());
        },

        /**
         * Returns whether the Map is empty
         * @returns {boolean}
         */
        isEmpty: function()
        {
            return 0 == this._count;
        },

        /**
         * Removes all key-value pairs from the map
         */
        clear: function()
        {
            this._map         = {};
            this._keys.length = 0;
            this._count       = 0;
        },

        /**
         * Removes a key-value pair from the Map
         * @param   {*}         key The key to remove
         * @returns {boolean}   Whether the pair was removed
         */
        remove: function(key)
        {
            if (!_hasKey(this._map, key)) {
                return false;
            }

            delete this._map[key];
            this._count--;
            if (this._keys.length > this._count * 2) {
                _cleanupKeys.call(this);
            }
            return true;
        },

        /**
         * Returns the value for the given key
         * @param   {*} key The key to look for
         * @param   {*} [defaultVal] The value to return if the key is not in the Map
         * @returns {*}
         */
        get: function(key, defaultVal)
        {
            if (_hasKey(this._map, key)) {
                return this._map[key];
            }
            return defaultVal;
        },

        /**
         * Adds a key-value pair to the Map
         * @param {*} key
         * @param {*} value
         */
        set: function(key, value)
        {
            if (!_hasKey(this._map, key)) {
                this._count++;
                this._keys.push(key);
            }
            this._map[key] = value;
        },

        /**
         * Merges key-value pairs from another Object or Map
         * @param {Object} map
         * @param {function(*, *)} [mergeFn] Optional function to call on values if
         *      both maps have the same key. By default a value from the map being
         *      merged will be stored under that key.
         */
        merge: function(map, mergeFn)
        {
            var keys, values, i = 0;
            if (map instanceof qf.Map) {
                keys   = map.getKeys();
                values = map.getValues();
            } else {
                keys   = [];
                values = [];
                for (var key in map) {
                    keys[i]     = key;
                    values[i++] = map[key];
                }
            }

            var fn = mergeFn || qf.Map.mergeReplace;

            for (i = 0; i < keys.length; i++) {
                if (!this.hasKey(keys[i])) {
                    this.set(keys[i], values[i]);
                } else {
                    this.set(keys[i], fn(this.get(keys[i]), values[i]));
                }
            }
        }
    };
})();

/**
 * Callback for merge(), forces to use second value.
 *
 * This makes Map.merge() behave like PHP's array_merge() function
 *
 * @param   {*} a Original value in map
 * @param   {*} b Value in the map being merged
 * @returns {*} second value
 */
qf.Map.mergeReplace = function(a, b)
{
    return b;
};

/**
 * Callback for merge(), forces to use first value.
 *
 * This makes Map.merge() behave like PHP's + operator for arrays
 *
 * @param   {*} a Original value in map
 * @param   {*} b Value in the map being merged
 * @returns {*} first value
 */
qf.Map.mergeKeep = function(a, b)
{
    return a;
};

/**
 * Callback for merge(), concatenates values.
 *
 * If the values are not arrays, they are first converted to ones.
 *
 * This callback makes Map.merge() behave somewhat like PHP's array_merge_recursive()
 *
 * @param   {*} a Original value in map
 * @param   {*} b Value in the map being merged
 * @returns {Array} array containing both values
 */
qf.Map.mergeArrayConcat = function(a, b)
{
    if ('array' != qf.typeOf(a)) {
        a = [a];
    }
    if ('array' != qf.typeOf(b)) {
        b = [b];
    }
    return a.concat(b);
};

