module.exports = {
	root: true,
	env: {
	  node: true,
	},
	extends: [
	  "plugin:vue/base",
	  "plugin:vue/vue3-strongly-recommended",
	  "plugin:vue/vue3-essential",
	  "plugin:vue/vue3-recommended",
	  "plugin:vuetify/base",
	  "plugin:vuetify/recommended",
	  "eslint:recommended",
	],
	rules: {
	  "semi": ["error", "always"],
	  "no-warning-comments": ["warn"],
	  "vue/no-v-model-argument": ["off"],
	  "camelcase": ["off", { properties: "always" }],
	  "vue/component-name-in-template-casing": [
			"error",
			"kebab-case",
			{
		  registeredComponentsOnly: true,
			},
	  ],
	  "vue/new-line-between-multi-line-property": [
			"error",
			{
		  minLineOfMultilineProperty: 2,
			},
	  ],
	  "vue/html-self-closing": [
			"error",
			{
		  html: {
					void: "never",
					normal: "always",
					component: "always",
		  },
		  svg: "always",
		  math: "always",
			},
	  ],
	  "vue/attributes-order": [
			"error",
			{
		  alphabetical: true,
			},
	  ],
	  "vue/custom-event-name-casing": ["error", "kebab-case"],
	  "no-unused-vars": [
			"error",
			{
		  argsIgnorePattern: "^_",
			}
	  ],
	  "vue/no-unused-properties": [
			"error",
			{
		  groups: ["props", "data"],
		  deepData: true,
		  ignorePublicMembers: false,
			},
	  ],
	  "vue/no-v-text": ["error"],
	  "vue/comma-style": ["error"],
	  "vue/require-name-property": ["error"],
	  "vue/padding-line-between-blocks": ["error", "always"],
	  "vue/static-class-names-order": ["error"],
	  "vue/padding-lines-in-component-definition": [
			"error",
			{
		  betweenOptions: "always",
			},
	  ],
	  "vue/require-default-prop": ["error"],
	  "vue/no-unsupported-features": [
			"warn",
			{
		  version: "^3.4.15",
		  ignores: [],
			},
	  ],
	  "vue/no-potential-component-option-typo": [
			"warn",
			{
		  presets: ["vue"],
		  custom: [],
		  threshold: 1,
			},
	  ],
	  "vue/no-empty-component-block": ["warn"],
	  "vue/no-multiple-template-root": ["warn"],
	  "vue/no-template-shadow": ["off"],
	  "vue/no-v-for-template-key": ["off"],
	  "vue/valid-v-slot": ["off"],
	  "vue/no-v-html": ["off"],
	},
};