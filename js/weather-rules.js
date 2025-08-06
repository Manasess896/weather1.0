function celsiusToFahrenheit(celsius) {
  const c = parseFloat(celsius);
  if (isNaN(c)) return "";
  return Math.round(((c * 9) / 5 + 32) * 100) / 100;
}

function fahrenheitToCelsius(fahrenheit) {
  const f = parseFloat(fahrenheit);
  if (isNaN(f)) return "";
  return Math.round((((f - 32) * 5) / 9) * 100) / 100;
}

const WEATHER_RULES = {
  temperatureRanges: {
    "hot-dry": {
      min: { celsius: 28, fahrenheit: 82 },
      max: { celsius: 40, fahrenheit: 104 },
      humidity: "Low",
      rain: "No",
      description: "Hot and Dry",
    },
    "warm-sunny": {
      min: { celsius: 21, fahrenheit: 70 },
      max: { celsius: 28, fahrenheit: 82 },
      humidity: "Low",
      rain: "No",
      description: "Warm and Sunny",
    },
    "cold-snowy": {
      min: { celsius: -10, fahrenheit: 14 },
      max: { celsius: 5, fahrenheit: 41 },
      humidity: "High",
      rain: "Snow",
      description: "Cold and Snowy",
    },
    "mild-rainy": {
      min: { celsius: 15, fahrenheit: 59 },
      max: { celsius: 25, fahrenheit: 77 },
      humidity: "High",
      rain: "Yes",
      description: "Mild and Rainy",
    },
    "cool-humid": {
      min: { celsius: 10, fahrenheit: 50 },
      max: { celsius: 20, fahrenheit: 68 },
      humidity: "High",
      rain: "Occasional",
      description: "Cool and Humid",
    },
    balanced: {
      min: { celsius: 18, fahrenheit: 64 },
      max: { celsius: 28, fahrenheit: 82 },
      humidity: "Moderate",
      rain: "Occasional",
      description: "Balanced",
    },
  },

  conflicts: {
    "hot-dry": ["cold-snowy", "cool-humid", "mild-rainy"],
    "warm-sunny": ["cold-snowy", "cool-humid"],
    "cold-snowy": ["hot-dry", "warm-sunny", "balanced"],
    "mild-rainy": ["hot-dry", "warm-sunny"],
    "cool-humid": ["hot-dry", "warm-sunny"],
    balanced: ["cold-snowy"],
  },

  autoAdjustments: {
    "hot-dry": {
      min: { celsius: 28, fahrenheit: 82 },
      max: { celsius: 40, fahrenheit: 104 },
    },
    "warm-sunny": {
      min: { celsius: 21, fahrenheit: 70 },
      max: { celsius: 28, fahrenheit: 82 },
    },
    "cold-snowy": {
      min: { celsius: -10, fahrenheit: 14 },
      max: { celsius: 5, fahrenheit: 41 },
    },
    "mild-rainy": {
      min: { celsius: 15, fahrenheit: 59 },
      max: { celsius: 25, fahrenheit: 77 },
    },
    "cool-humid": {
      min: { celsius: 10, fahrenheit: 50 },
      max: { celsius: 20, fahrenheit: 68 },
    },
    balanced: {
      min: { celsius: 18, fahrenheit: 64 },
      max: { celsius: 28, fahrenheit: 82 },
    },
  },

  default: {
    min: { celsius: -30, fahrenheit: -22 },
    max: { celsius: 55, fahrenheit: 131 },
  },

  messages: {
    "hot-dry":
      "Selecting 'Hot and Dry' has adjusted temperature settings for hot, arid conditions.",
    "warm-sunny":
      "Selecting 'Warm and Sunny' has adjusted temperature settings for pleasant warm weather.",
    "cold-snowy":
      "Selecting 'Cold and Snowy' has adjusted conflicting preferences and temperature for winter conditions.",
    "mild-rainy":
      "Selecting 'Mild and Rainy' has adjusted temperature settings for rainy weather conditions.",
    "cool-humid":
      "Selecting 'Cool and Humid' has adjusted temperature settings for cool, moist conditions.",
    balanced:
      "Selecting 'Balanced' has set moderate temperature settings for comfortable weather.",
  },
};

const WeatherRulesUtils = {
  /**
   *
   * @param {string} preference - W
   * @param {string} unit -
   * @returns {object}
   */
  getTemperatureRange(preference, unit = "celsius") {
    const range = WEATHER_RULES.temperatureRanges[preference];
    if (!range) return WEATHER_RULES.default;
    return {
      min: parseFloat(range.min[unit]),
      max: parseFloat(range.max[unit]),
      recommendedMin: range.recommended
        ? parseFloat(range.recommended.min[unit])
        : undefined,
      recommendedMax: range.recommended
        ? parseFloat(range.recommended.max[unit])
        : undefined,
    };
  },

  /**
   
   * @param {string} preference - 
   * @returns {array} 
   */
  getConflicts(preference) {
    return WEATHER_RULES.conflicts[preference] || [];
  },

  /**
   * @param {string} preference -
   * @param {string} unit -
   * @returns {object|null}
   */
  getAutoAdjustment(preference, unit = "celsius") {
    const adjustment = WEATHER_RULES.autoAdjustments[preference];
    if (!adjustment) return null;

    return {
      min: parseFloat(adjustment.min[unit]),
      max: parseFloat(adjustment.max[unit]),
    };
  },

  /**
   * @param {string} preference -
   * @returns {string}
   */
  getMessage(preference) {
    return WEATHER_RULES.messages[preference] || "";
  },

  /**
   
   * @param {number} minTemp 
   * @param {number} maxTemp 
   * @param {array} preferences 
   * @param {string} unit 
   * @returns {object} 
   */
  validateTemperatures(minTemp, maxTemp, preferences, unit = "celsius") {
    const errors = [];
    const warnings = [];
    const unitSymbol = unit === "celsius" ? "C" : "F";

    if (minTemp > maxTemp) {
      errors.push(
        "Minimum temperature must be lower than maximum temperature."
      );
    }
    preferences.forEach((pref) => {
      const range = this.getTemperatureRange(pref, unit);
      if (!range || range === WEATHER_RULES.default) return;

      if (maxTemp < range.min) {
        errors.push(
          `"${this.getPreferenceLabel(pref)}" requires temperatures above ${
            range.min
          }°${unitSymbol}. Your maximum (${maxTemp}°${unitSymbol}) is too low.`
        );
      }
      if (minTemp > range.max) {
        errors.push(
          `"${this.getPreferenceLabel(pref)}" requires temperatures below ${
            range.max
          }°${unitSymbol}. Your minimum (${minTemp}°${unitSymbol}) is too high.`
        );
      }

      if (range.recommendedMin && maxTemp < range.recommendedMin) {
        warnings.push(
          `"${this.getPreferenceLabel(
            pref
          )}" works best with temperatures above ${
            range.recommendedMin
          }°${unitSymbol}.`
        );
      }
      if (range.recommendedMax && minTemp > range.recommendedMax) {
        warnings.push(
          `"${this.getPreferenceLabel(
            pref
          )}" works best with temperatures below ${
            range.recommendedMax
          }°${unitSymbol}.`
        );
      }
    });

    return { errors, warnings };
  },

  /**
   * @param {string} preference
   * @returns {string}
   */
  getPreferenceLabel(preference) {
    const labels = {
      "hot-dry": "Hot and Dry",
      "warm-sunny": "Warm and Sunny",
      "cold-snowy": "Cold and Snowy",
      "mild-rainy": "Mild and Rainy",
      "cool-humid": "Cool and Humid",
      balanced: "Balanced",
    };
    return labels[preference] || preference;
  },

  /**
   * Get profile metadata (humidity, rain, etc.)
   * @param {string} preference
   * @returns {object}
   */
  getProfileMetadata(preference) {
    const range = WEATHER_RULES.temperatureRanges[preference];
    if (!range) return {};

    return {
      humidity: range.humidity || "Moderate",
      rain: range.rain || "Occasional",
      description: range.description || preference,
    };
  },

  /**
   * et all available weather profiles
   * @returns {array}
   */
  getAvailableProfiles() {
    return Object.keys(WEATHER_RULES.temperatureRanges).map((key) => ({
      value: key,
      label: this.getPreferenceLabel(key),
      description: WEATHER_RULES.temperatureRanges[key].description || key,
    }));
  },
};
if (typeof window !== "undefined") {
  window.WEATHER_RULES = WEATHER_RULES;
  window.WeatherRulesUtils = WeatherRulesUtils;
  window.celsiusToFahrenheit = celsiusToFahrenheit;
  window.fahrenheitToCelsius = fahrenheitToCelsius;
}
if (typeof module !== "undefined" && module.exports) {
  module.exports = {
    WEATHER_RULES,
    WeatherRulesUtils,
    celsiusToFahrenheit,
    fahrenheitToCelsius,
  };
}
