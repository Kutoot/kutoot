import { jsx, jsxs, Fragment as Fragment$1 } from "react/jsx-runtime";
import React2, { forwardRef, useRef, useImperativeHandle, useEffect, createContext, useMemo, useState, createElement, useCallback, useLayoutEffect, Fragment, useContext } from "react";
import { config as config$1, isUrlMethodPair, mergeDataIntoQueryString, getScrollableParent, useInfiniteScroll, router, UseFormUtils, formDataToObject, FormComponentResetSymbol, resetFormFields, shouldIntercept, shouldNavigate, getInitialPageFromDOM, setupProgress, createHeadManager } from "@inertiajs/core";
import { flushSync } from "react-dom";
import { cloneDeep, isEqual, set, has, get, escape as escape$1 } from "lodash-es";
import { createValidator, toSimpleValidationErrors, resolveName } from "laravel-precognition";
import { Transition, Dialog, TransitionChild, DialogPanel } from "@headlessui/react";
import axios from "axios";
import createServer from "@inertiajs/core/server";
import ReactDOMServer from "react-dom/server";
function InputError({ message, className = "", ...props }) {
  return message ? /* @__PURE__ */ jsx(
    "p",
    {
      ...props,
      className: "text-sm text-red-600 " + className,
      children: message
    }
  ) : null;
}
function InputLabel({
  value,
  className = "",
  children,
  ...props
}) {
  return /* @__PURE__ */ jsx(
    "label",
    {
      ...props,
      className: `block text-sm font-medium text-gray-700 ` + className,
      children: value ? value : children
    }
  );
}
function PrimaryButton({
  className = "",
  disabled,
  children,
  ...props
}) {
  return /* @__PURE__ */ jsx(
    "button",
    {
      ...props,
      className: `inline-flex items-center rounded-full border border-transparent bg-gradient-to-r from-lucky-500 to-lucky-600 px-5 py-2.5 text-xs font-bold uppercase tracking-widest text-white shadow-md transition duration-150 ease-in-out hover:from-lucky-600 hover:to-ticket-500 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-lucky-400 focus:ring-offset-2 active:from-lucky-700 active:to-ticket-600 transform hover:-translate-y-0.5 ${disabled && "opacity-25 hover:translate-y-0"} ` + className,
      disabled,
      children
    }
  );
}
const TextInput = forwardRef(function TextInput2({ type = "text", className = "", isFocused = false, ...props }, ref) {
  const localRef = useRef(null);
  useImperativeHandle(ref, () => ({
    focus: () => localRef.current?.focus()
  }));
  useEffect(() => {
    if (isFocused) {
      localRef.current?.focus();
    }
  }, [isFocused]);
  return /* @__PURE__ */ jsx(
    "input",
    {
      ...props,
      type,
      className: "rounded-lg border-lucky-200 shadow-sm focus:border-lucky-500 focus:ring-lucky-500 " + className,
      ref: localRef
    }
  );
});
function ApplicationLogo({ className = "", ...props }) {
  return /* @__PURE__ */ jsx(
    "img",
    {
      src: "/images/kutoot-name-logo.svg",
      alt: "Kutoot",
      className: `h-10 w-auto ${className}`,
      ...props
    }
  );
}
var headContext = createContext(null);
headContext.displayName = "InertiaHeadContext";
var HeadContext_default = headContext;
var pageContext = createContext(null);
pageContext.displayName = "InertiaPageContext";
var PageContext_default = pageContext;
var currentIsInitialPage = true;
var routerIsInitialized = false;
var swapComponent = async () => {
  currentIsInitialPage = false;
};
function App({
  children,
  initialPage,
  initialComponent,
  resolveComponent,
  titleCallback,
  onHeadUpdate
}) {
  const [current, setCurrent] = useState({
    component: initialComponent || null,
    page: { ...initialPage, flash: initialPage.flash ?? {} },
    key: null
  });
  const headManager = useMemo(() => {
    return createHeadManager(
      typeof window === "undefined",
      titleCallback || ((title) => title),
      onHeadUpdate || (() => {
      })
    );
  }, []);
  if (!routerIsInitialized) {
    router.init({
      initialPage,
      resolveComponent,
      swapComponent: async (args) => swapComponent(args),
      onFlash: (flash) => {
        setCurrent((current2) => ({
          ...current2,
          page: { ...current2.page, flash }
        }));
      }
    });
    routerIsInitialized = true;
  }
  useEffect(() => {
    swapComponent = async ({ component, page, preserveState }) => {
      if (currentIsInitialPage) {
        currentIsInitialPage = false;
        return;
      }
      flushSync(
        () => setCurrent((current2) => ({
          component,
          page,
          key: preserveState ? current2.key : Date.now()
        }))
      );
    };
    router.on("navigate", () => headManager.forceUpdate());
  }, []);
  if (!current.component) {
    return createElement(
      HeadContext_default.Provider,
      { value: headManager },
      createElement(PageContext_default.Provider, { value: current.page }, null)
    );
  }
  const renderChildren = children || (({ Component, props, key }) => {
    const child = createElement(Component, { key, ...props });
    if (typeof Component.layout === "function") {
      return Component.layout(child);
    }
    if (Array.isArray(Component.layout)) {
      return Component.layout.concat(child).reverse().reduce((children2, Layout) => createElement(Layout, { children: children2, ...props }));
    }
    return child;
  });
  return createElement(
    HeadContext_default.Provider,
    { value: headManager },
    createElement(
      PageContext_default.Provider,
      { value: current.page },
      renderChildren({
        Component: current.component,
        key: current.key,
        props: current.page.props
      })
    )
  );
}
App.displayName = "Inertia";
async function createInertiaApp({
  id = "app",
  resolve,
  setup,
  title,
  progress: progress2 = {},
  page,
  render,
  defaults = {}
}) {
  config.replace(defaults);
  const isServer = typeof window === "undefined";
  const useScriptElementForInitialPage = config.get("future.useScriptElementForInitialPage");
  const initialPage = page || getInitialPageFromDOM(id, useScriptElementForInitialPage);
  const resolveComponent = (name) => Promise.resolve(resolve(name)).then((module) => module.default || module);
  let head = [];
  const reactApp = await Promise.all([
    resolveComponent(initialPage.component),
    router.decryptHistory().catch(() => {
    })
  ]).then(([initialComponent]) => {
    const props = {
      initialPage,
      initialComponent,
      resolveComponent,
      titleCallback: title
    };
    if (isServer) {
      const ssrSetup = setup;
      return ssrSetup({
        el: null,
        App,
        props: { ...props, onHeadUpdate: (elements) => head = elements }
      });
    }
    const csrSetup = setup;
    return csrSetup({
      el: document.getElementById(id),
      App,
      props
    });
  });
  if (!isServer && progress2) {
    setupProgress(progress2);
  }
  if (isServer && render) {
    const element = () => {
      if (!useScriptElementForInitialPage) {
        return createElement(
          "div",
          {
            id,
            "data-page": JSON.stringify(initialPage)
          },
          reactApp
        );
      }
      return createElement(
        Fragment,
        null,
        createElement("script", {
          "data-page": id,
          type: "application/json",
          dangerouslySetInnerHTML: { __html: JSON.stringify(initialPage).replace(/\//g, "\\/") }
        }),
        createElement("div", { id }, reactApp)
      );
    };
    const body = await render(element());
    return { head, body };
  }
}
function useIsomorphicLayoutEffect(effect, deps) {
  typeof window === "undefined" ? useEffect(effect, deps) : useLayoutEffect(effect, deps);
}
var isReact19 = typeof React2.use === "function";
function usePage() {
  const page = isReact19 ? React2.use(PageContext_default) : React2.useContext(PageContext_default);
  if (!page) {
    throw new Error("usePage must be used within the Inertia component");
  }
  return page;
}
function useRemember(initialState, key, excludeKeysRef) {
  const [state, setState] = useState(() => {
    const restored = router.restore(key);
    return restored !== void 0 ? restored : initialState;
  });
  useEffect(() => {
    const keys = excludeKeysRef?.current;
    if (keys && keys.length > 0 && typeof state === "object" && state !== null) {
      const filtered = { ...state };
      keys.forEach((k) => delete filtered[k]);
      router.remember(filtered, key);
    } else {
      router.remember(state, key);
    }
  }, [state, key]);
  return [state, setState];
}
function useForm(...args) {
  const isMounted = useRef(false);
  const parsedArgs = UseFormUtils.parseUseFormArguments(...args);
  const { rememberKey, data: initialData } = parsedArgs;
  const precognitionEndpoint = useRef(parsedArgs.precognitionEndpoint);
  const [defaults, setDefaults] = useState(
    typeof initialData === "function" ? cloneDeep(initialData()) : cloneDeep(initialData)
  );
  const cancelToken = useRef(null);
  const recentlySuccessfulTimeoutId = useRef(void 0);
  const excludeKeysRef = useRef([]);
  const [data, setData] = rememberKey ? useRemember(defaults, `${rememberKey}:data`, excludeKeysRef) : useState(defaults);
  const [errors, setErrors] = rememberKey ? useRemember({}, `${rememberKey}:errors`) : useState({});
  const [hasErrors, setHasErrors] = useState(false);
  const [processing, setProcessing] = useState(false);
  const [progress2, setProgress] = useState(null);
  const [wasSuccessful, setWasSuccessful] = useState(false);
  const [recentlySuccessful, setRecentlySuccessful] = useState(false);
  const transform = useRef((data2) => data2);
  const isDirty = useMemo(() => !isEqual(data, defaults), [data, defaults]);
  const validatorRef = useRef(null);
  const [validating, setValidating] = useState(false);
  const [touchedFields, setTouchedFields] = useState([]);
  const [validFields, setValidFields] = useState([]);
  const withAllErrors = useRef(null);
  useEffect(() => {
    isMounted.current = true;
    return () => {
      isMounted.current = false;
    };
  }, []);
  const setDefaultsCalledInOnSuccess = useRef(false);
  const submit = useCallback(
    (...args2) => {
      const { method, url, options } = UseFormUtils.parseSubmitArguments(args2, precognitionEndpoint.current);
      setDefaultsCalledInOnSuccess.current = false;
      const _options = {
        ...options,
        onCancelToken: (token) => {
          cancelToken.current = token;
          if (options.onCancelToken) {
            return options.onCancelToken(token);
          }
        },
        onBefore: (visit) => {
          setWasSuccessful(false);
          setRecentlySuccessful(false);
          clearTimeout(recentlySuccessfulTimeoutId.current);
          if (options.onBefore) {
            return options.onBefore(visit);
          }
        },
        onStart: (visit) => {
          setProcessing(true);
          if (options.onStart) {
            return options.onStart(visit);
          }
        },
        onProgress: (event) => {
          setProgress(event || null);
          if (options.onProgress) {
            return options.onProgress(event);
          }
        },
        onSuccess: async (page) => {
          if (isMounted.current) {
            setProcessing(false);
            setProgress(null);
            setErrors({});
            setHasErrors(false);
            setWasSuccessful(true);
            setRecentlySuccessful(true);
            recentlySuccessfulTimeoutId.current = setTimeout(() => {
              if (isMounted.current) {
                setRecentlySuccessful(false);
              }
            }, config.get("form.recentlySuccessfulDuration"));
          }
          const onSuccess = options.onSuccess ? await options.onSuccess(page) : null;
          if (isMounted.current && !setDefaultsCalledInOnSuccess.current) {
            setData((data2) => {
              setDefaults(cloneDeep(data2));
              return data2;
            });
          }
          return onSuccess;
        },
        onError: (errors2) => {
          if (isMounted.current) {
            setProcessing(false);
            setProgress(null);
            setErrors(errors2);
            setHasErrors(Object.keys(errors2).length > 0);
            validatorRef.current?.setErrors(errors2);
          }
          if (options.onError) {
            return options.onError(errors2);
          }
        },
        onCancel: () => {
          if (isMounted.current) {
            setProcessing(false);
            setProgress(null);
          }
          if (options.onCancel) {
            return options.onCancel();
          }
        },
        onFinish: (visit) => {
          if (isMounted.current) {
            setProcessing(false);
            setProgress(null);
          }
          cancelToken.current = null;
          if (options.onFinish) {
            return options.onFinish(visit);
          }
        }
      };
      const transformedData = transform.current(data);
      if (method === "delete") {
        router.delete(url, { ..._options, data: transformedData });
      } else {
        router[method](url, transformedData, _options);
      }
    },
    [data, setErrors, transform]
  );
  const setDataFunction = useCallback(
    (keyOrData, maybeValue) => {
      if (typeof keyOrData === "string") {
        setData((data2) => set(cloneDeep(data2), keyOrData, maybeValue));
      } else if (typeof keyOrData === "function") {
        setData((data2) => keyOrData(data2));
      } else {
        setData(keyOrData);
      }
    },
    [setData]
  );
  const [dataAsDefaults, setDataAsDefaults] = useState(false);
  const dataRef = useRef(data);
  useEffect(() => {
    dataRef.current = data;
  });
  const setDefaultsFunction = useCallback(
    (fieldOrFields, maybeValue) => {
      setDefaultsCalledInOnSuccess.current = true;
      let newDefaults = {};
      if (typeof fieldOrFields === "undefined") {
        newDefaults = { ...dataRef.current };
        setDefaults(dataRef.current);
        setDataAsDefaults(true);
      } else {
        setDefaults((defaults2) => {
          newDefaults = typeof fieldOrFields === "string" ? set(cloneDeep(defaults2), fieldOrFields, maybeValue) : Object.assign(cloneDeep(defaults2), fieldOrFields);
          return newDefaults;
        });
      }
      validatorRef.current?.defaults(newDefaults);
    },
    [setDefaults]
  );
  useIsomorphicLayoutEffect(() => {
    if (!dataAsDefaults) {
      return;
    }
    if (isDirty) {
      setDefaults(data);
    }
    setDataAsDefaults(false);
  }, [dataAsDefaults]);
  const reset = useCallback(
    (...fields) => {
      if (fields.length === 0) {
        setData(defaults);
      } else {
        setData(
          (data2) => fields.filter((key) => has(defaults, key)).reduce(
            (carry, key) => {
              return set(carry, key, get(defaults, key));
            },
            { ...data2 }
          )
        );
      }
      validatorRef.current?.reset(...fields);
    },
    [setData, defaults]
  );
  const setError = useCallback(
    (fieldOrFields, maybeValue) => {
      setErrors((errors2) => {
        const newErrors = {
          ...errors2,
          ...typeof fieldOrFields === "string" ? { [fieldOrFields]: maybeValue } : fieldOrFields
        };
        setHasErrors(Object.keys(newErrors).length > 0);
        validatorRef.current?.setErrors(newErrors);
        return newErrors;
      });
    },
    [setErrors, setHasErrors]
  );
  const clearErrors = useCallback(
    (...fields) => {
      setErrors((errors2) => {
        const newErrors = Object.keys(errors2).reduce(
          (carry, field) => ({
            ...carry,
            ...fields.length > 0 && !fields.includes(field) ? { [field]: errors2[field] } : {}
          }),
          {}
        );
        setHasErrors(Object.keys(newErrors).length > 0);
        if (validatorRef.current) {
          if (fields.length === 0) {
            validatorRef.current.setErrors({});
          } else {
            fields.forEach(validatorRef.current.forgetError);
          }
        }
        return newErrors;
      });
    },
    [setErrors, setHasErrors]
  );
  const resetAndClearErrors = useCallback(
    (...fields) => {
      reset(...fields);
      clearErrors(...fields);
    },
    [reset, clearErrors]
  );
  const createSubmitMethod = (method) => (url, options = {}) => {
    submit(method, url, options);
  };
  const getMethod = useCallback(createSubmitMethod("get"), [submit]);
  const post = useCallback(createSubmitMethod("post"), [submit]);
  const put = useCallback(createSubmitMethod("put"), [submit]);
  const patch = useCallback(createSubmitMethod("patch"), [submit]);
  const deleteMethod = useCallback(createSubmitMethod("delete"), [submit]);
  const cancel = useCallback(() => {
    if (cancelToken.current) {
      cancelToken.current.cancel();
    }
  }, []);
  const transformFunction = useCallback((callback) => {
    transform.current = callback;
  }, []);
  const form = {
    data,
    setData: setDataFunction,
    isDirty,
    errors,
    hasErrors,
    processing,
    progress: progress2,
    wasSuccessful,
    recentlySuccessful,
    transform: transformFunction,
    setDefaults: setDefaultsFunction,
    reset,
    setError,
    clearErrors,
    resetAndClearErrors,
    submit,
    get: getMethod,
    post,
    put,
    patch,
    delete: deleteMethod,
    cancel,
    dontRemember: (...keys) => {
      excludeKeysRef.current = keys;
      return form;
    }
  };
  const tap = (value, callback) => {
    callback(value);
    return value;
  };
  const valid = useCallback(
    (field) => validFields.includes(field),
    [validFields]
  );
  const invalid = useCallback((field) => field in errors, [errors]);
  const touched = useCallback(
    (field) => typeof field === "string" ? touchedFields.includes(field) : touchedFields.length > 0,
    [touchedFields]
  );
  const validate = (field, config3) => {
    if (typeof field === "object" && !("target" in field)) {
      config3 = field;
      field = void 0;
    }
    if (field === void 0) {
      validatorRef.current.validate(config3);
    } else {
      const fieldName = resolveName(field);
      const currentData = dataRef.current;
      const transformedData = transform.current(currentData);
      validatorRef.current.validate(fieldName, get(transformedData, fieldName), config3);
    }
    return form;
  };
  const withPrecognition = (...args2) => {
    precognitionEndpoint.current = UseFormUtils.createWayfinderCallback(...args2);
    if (!validatorRef.current) {
      const validator = createValidator((client) => {
        const { method, url } = precognitionEndpoint.current();
        const currentData = dataRef.current;
        const transformedData = transform.current(currentData);
        return client[method](url, transformedData);
      }, cloneDeep(defaults));
      validatorRef.current = validator;
      validator.on("validatingChanged", () => {
        setValidating(validator.validating());
      }).on("validatedChanged", () => {
        setValidFields(validator.valid());
      }).on("touchedChanged", () => {
        setTouchedFields(validator.touched());
      }).on("errorsChanged", () => {
        const validationErrors = withAllErrors.current ?? config.get("form.withAllErrors") ? validator.errors() : toSimpleValidationErrors(validator.errors());
        setErrors(validationErrors);
        setHasErrors(Object.keys(validationErrors).length > 0);
        setValidFields(validator.valid());
      });
    }
    const precognitiveForm = Object.assign(form, {
      validating,
      validator: () => validatorRef.current,
      valid,
      invalid,
      touched,
      withoutFileValidation: () => tap(precognitiveForm, () => validatorRef.current?.withoutFileValidation()),
      touch: (field, ...fields) => {
        if (Array.isArray(field)) {
          validatorRef.current?.touch(field);
        } else if (typeof field === "string") {
          validatorRef.current?.touch([field, ...fields]);
        } else {
          validatorRef.current?.touch(field);
        }
        return precognitiveForm;
      },
      withAllErrors: () => tap(precognitiveForm, () => withAllErrors.current = true),
      setValidationTimeout: (duration) => tap(precognitiveForm, () => validatorRef.current?.setTimeout(duration)),
      validateFiles: () => tap(precognitiveForm, () => validatorRef.current?.validateFiles()),
      validate,
      setErrors: (errors2) => tap(precognitiveForm, () => form.setError(errors2)),
      forgetError: (field) => tap(
        precognitiveForm,
        () => form.clearErrors(resolveName(field))
      )
    });
    return precognitiveForm;
  };
  form.withPrecognition = withPrecognition;
  return precognitionEndpoint.current ? form.withPrecognition(precognitionEndpoint.current) : form;
}
var deferStateUpdate = (callback) => {
  typeof React2.startTransition === "function" ? React2.startTransition(callback) : setTimeout(callback, 0);
};
var noop = () => void 0;
var FormContext = createContext(void 0);
var Form = forwardRef(
  ({
    action = "",
    method = "get",
    headers = {},
    queryStringArrayFormat = "brackets",
    errorBag = null,
    showProgress = true,
    transform = (data) => data,
    options = {},
    onStart = noop,
    onProgress = noop,
    onFinish = noop,
    onBefore = noop,
    onCancel = noop,
    onSuccess = noop,
    onError = noop,
    onCancelToken = noop,
    onSubmitComplete = noop,
    disableWhileProcessing = false,
    resetOnError = false,
    resetOnSuccess = false,
    setDefaultsOnSuccess = false,
    invalidateCacheTags = [],
    validateFiles = false,
    validationTimeout = 1500,
    withAllErrors = null,
    children,
    ...props
  }, ref) => {
    const getTransformedData = () => {
      const [_url, data] = getUrlAndData();
      return transform(data);
    };
    const form = useForm({}).withPrecognition(
      () => resolvedMethod,
      () => getUrlAndData()[0]
    ).setValidationTimeout(validationTimeout);
    if (validateFiles) {
      form.validateFiles();
    }
    if (withAllErrors ?? config$1.get("form.withAllErrors")) {
      form.withAllErrors();
    }
    form.transform(getTransformedData);
    const formElement = useRef(void 0);
    const resolvedMethod = useMemo(() => {
      return isUrlMethodPair(action) ? action.method : method.toLowerCase();
    }, [action, method]);
    const [isDirty, setIsDirty] = useState(false);
    const defaultData = useRef(new FormData());
    const getFormData = (submitter) => new FormData(formElement.current, submitter);
    const getData = (submitter) => formDataToObject(getFormData(submitter));
    const getUrlAndData = (submitter) => {
      return mergeDataIntoQueryString(
        resolvedMethod,
        isUrlMethodPair(action) ? action.url : action,
        getData(submitter),
        queryStringArrayFormat
      );
    };
    const updateDirtyState = (event) => {
      if (event.type === "reset" && event.detail?.[FormComponentResetSymbol]) {
        event.preventDefault();
      }
      deferStateUpdate(
        () => setIsDirty(event.type === "reset" ? false : !isEqual(getData(), formDataToObject(defaultData.current)))
      );
    };
    const clearErrors = (...names) => {
      form.clearErrors(...names);
      return form;
    };
    useEffect(() => {
      defaultData.current = getFormData();
      form.setDefaults(getData());
      const formEvents = ["input", "change", "reset"];
      formEvents.forEach((e2) => formElement.current.addEventListener(e2, updateDirtyState));
      return () => {
        formEvents.forEach((e2) => formElement.current?.removeEventListener(e2, updateDirtyState));
      };
    }, []);
    useEffect(() => {
      form.setValidationTimeout(validationTimeout);
    }, [validationTimeout]);
    useEffect(() => {
      if (validateFiles) {
        form.validateFiles();
      } else {
        form.withoutFileValidation();
      }
    }, [validateFiles]);
    const reset = (...fields) => {
      if (formElement.current) {
        resetFormFields(formElement.current, defaultData.current, fields);
      }
      form.reset(...fields);
    };
    const resetAndClearErrors = (...fields) => {
      clearErrors(...fields);
      reset(...fields);
    };
    const maybeReset = (resetOption) => {
      if (!resetOption) {
        return;
      }
      if (resetOption === true) {
        reset();
      } else if (resetOption.length > 0) {
        reset(...resetOption);
      }
    };
    const submit = (submitter) => {
      const [url, data] = getUrlAndData(submitter);
      const formTarget = submitter?.getAttribute("formtarget");
      if (formTarget === "_blank" && resolvedMethod === "get") {
        window.open(url, "_blank");
        return;
      }
      const submitOptions = {
        headers,
        queryStringArrayFormat,
        errorBag,
        showProgress,
        invalidateCacheTags,
        onCancelToken,
        onBefore,
        onStart,
        onProgress,
        onFinish,
        onCancel,
        onSuccess: (...args) => {
          onSuccess(...args);
          onSubmitComplete({
            reset,
            defaults
          });
          maybeReset(resetOnSuccess);
          if (setDefaultsOnSuccess === true) {
            defaults();
          }
        },
        onError(...args) {
          onError(...args);
          maybeReset(resetOnError);
        },
        ...options
      };
      form.transform(() => transform(data));
      form.submit(resolvedMethod, url, submitOptions);
      form.transform(getTransformedData);
    };
    const defaults = () => {
      defaultData.current = getFormData();
      setIsDirty(false);
    };
    const exposed = {
      errors: form.errors,
      hasErrors: form.hasErrors,
      processing: form.processing,
      progress: form.progress,
      wasSuccessful: form.wasSuccessful,
      recentlySuccessful: form.recentlySuccessful,
      isDirty,
      clearErrors,
      resetAndClearErrors,
      setError: form.setError,
      reset,
      submit,
      defaults,
      getData,
      getFormData,
      // Precognition
      validator: () => form.validator(),
      validating: form.validating,
      valid: form.valid,
      invalid: form.invalid,
      validate: (field, config3) => form.validate(...UseFormUtils.mergeHeadersForValidation(field, config3, headers)),
      touch: form.touch,
      touched: form.touched
    };
    useImperativeHandle(ref, () => exposed, [form, isDirty, submit]);
    const formNode = createElement(
      "form",
      {
        ...props,
        ref: formElement,
        action: isUrlMethodPair(action) ? action.url : action,
        method: resolvedMethod,
        onSubmit: (event) => {
          event.preventDefault();
          submit(event.nativeEvent.submitter);
        },
        // React 19 supports passing a boolean to the `inert` attribute, but shows
        // a warning when receiving a string. Earlier versions require the string 'true'.
        // See: https://github.com/inertiajs/inertia/pull/2536
        inert: disableWhileProcessing && form.processing && (isReact19 ? true : "true")
      },
      typeof children === "function" ? children(exposed) : children
    );
    return createElement(FormContext.Provider, { value: exposed }, formNode);
  }
);
Form.displayName = "InertiaForm";
var Head = function({ children, title }) {
  const headManager = useContext(HeadContext_default);
  const provider = useMemo(() => headManager.createProvider(), [headManager]);
  const isServer = typeof window === "undefined";
  useEffect(() => {
    provider.reconnect();
    provider.update(renderNodes(children));
    return () => {
      provider.disconnect();
    };
  }, [provider, children, title]);
  function isUnaryTag(node) {
    return typeof node.type === "string" && [
      "area",
      "base",
      "br",
      "col",
      "embed",
      "hr",
      "img",
      "input",
      "keygen",
      "link",
      "meta",
      "param",
      "source",
      "track",
      "wbr"
    ].indexOf(node.type) > -1;
  }
  function renderTagStart(node) {
    const attrs = Object.keys(node.props).reduce((carry, name) => {
      if (["head-key", "children", "dangerouslySetInnerHTML"].includes(name)) {
        return carry;
      }
      const value = String(node.props[name]);
      if (value === "") {
        return carry + ` ${name}`;
      }
      return carry + ` ${name}="${escape$1(value)}"`;
    }, "");
    return `<${String(node.type)}${attrs}>`;
  }
  function renderTagChildren(node) {
    const { children: children2 } = node.props;
    if (typeof children2 === "string") {
      return children2;
    }
    if (Array.isArray(children2)) {
      return children2.reduce((html, child) => html + renderTag(child), "");
    }
    return "";
  }
  function renderTag(node) {
    let html = renderTagStart(node);
    if (node.props.children) {
      html += renderTagChildren(node);
    }
    if (node.props.dangerouslySetInnerHTML) {
      html += node.props.dangerouslySetInnerHTML.__html;
    }
    if (!isUnaryTag(node)) {
      html += `</${String(node.type)}>`;
    }
    return html;
  }
  function ensureNodeHasInertiaProp(node) {
    return React2.cloneElement(node, {
      [provider.preferredAttribute()]: node.props["head-key"] !== void 0 ? node.props["head-key"] : ""
    });
  }
  function renderNode(node) {
    return renderTag(ensureNodeHasInertiaProp(node));
  }
  function renderNodes(nodes) {
    const elements = React2.Children.toArray(nodes).filter((node) => node).map((node) => renderNode(node));
    if (title && !elements.find((tag) => tag.startsWith("<title"))) {
      elements.push(`<title ${provider.preferredAttribute()}>${title}</title>`);
    }
    return elements;
  }
  if (isServer) {
    provider.update(renderNodes(children));
  }
  return null;
};
var Head_default = Head;
var resolveHTMLElement = (value, fallback) => {
  if (!value) {
    return fallback;
  }
  if (value && typeof value === "object" && "current" in value) {
    return value.current;
  }
  if (typeof value === "string") {
    return document.querySelector(value);
  }
  return fallback;
};
var renderSlot = (slotContent, slotProps, fallback = null) => {
  if (!slotContent) {
    return fallback;
  }
  return typeof slotContent === "function" ? slotContent(slotProps) : slotContent;
};
var InfiniteScroll = forwardRef(
  ({
    data,
    buffer = 0,
    as = "div",
    manual = false,
    manualAfter = 0,
    preserveUrl = false,
    reverse = false,
    autoScroll,
    children,
    startElement,
    endElement,
    itemsElement,
    previous,
    next,
    loading,
    onlyNext = false,
    onlyPrevious = false,
    ...props
  }, ref) => {
    const [startElementFromRef, setStartElementFromRef] = useState(null);
    const startElementRef = useCallback((node) => setStartElementFromRef(node), []);
    const [endElementFromRef, setEndElementFromRef] = useState(null);
    const endElementRef = useCallback((node) => setEndElementFromRef(node), []);
    const [itemsElementFromRef, setItemsElementFromRef] = useState(null);
    const itemsElementRef = useCallback((node) => setItemsElementFromRef(node), []);
    const [loadingPrevious, setLoadingPrevious] = useState(false);
    const [loadingNext, setLoadingNext] = useState(false);
    const [requestCount, setRequestCount] = useState(0);
    const [hasPreviousPage, setHasPreviousPage] = useState(false);
    const [hasNextPage, setHasNextPage] = useState(false);
    const [resolvedStartElement, setResolvedStartElement] = useState(null);
    const [resolvedEndElement, setResolvedEndElement] = useState(null);
    const [resolvedItemsElement, setResolvedItemsElement] = useState(null);
    useEffect(() => {
      const element = startElement ? resolveHTMLElement(startElement, startElementFromRef) : startElementFromRef;
      setResolvedStartElement(element);
    }, [startElement, startElementFromRef]);
    useEffect(() => {
      const element = endElement ? resolveHTMLElement(endElement, endElementFromRef) : endElementFromRef;
      setResolvedEndElement(element);
    }, [endElement, endElementFromRef]);
    useEffect(() => {
      const element = itemsElement ? resolveHTMLElement(itemsElement, itemsElementFromRef) : itemsElementFromRef;
      setResolvedItemsElement(element);
    }, [itemsElement, itemsElementFromRef]);
    const scrollableParent = useMemo(() => getScrollableParent(resolvedItemsElement), [resolvedItemsElement]);
    const callbackPropsRef = useRef({
      buffer,
      onlyNext,
      onlyPrevious,
      reverse,
      preserveUrl
    });
    callbackPropsRef.current = {
      buffer,
      onlyNext,
      onlyPrevious,
      reverse,
      preserveUrl
    };
    const [infiniteScroll, setInfiniteScroll] = useState(null);
    const dataManager = useMemo(() => infiniteScroll?.dataManager, [infiniteScroll]);
    const elementManager = useMemo(() => infiniteScroll?.elementManager, [infiniteScroll]);
    const scrollToBottom = useCallback(() => {
      if (scrollableParent) {
        scrollableParent.scrollTo({
          top: scrollableParent.scrollHeight,
          behavior: "instant"
        });
      } else {
        window.scrollTo({
          top: document.body.scrollHeight,
          behavior: "instant"
        });
      }
    }, [scrollableParent]);
    useEffect(() => {
      if (!resolvedItemsElement) {
        return;
      }
      function syncStateFromDataManager() {
        setRequestCount(infiniteScrollInstance.dataManager.getRequestCount());
        setHasPreviousPage(infiniteScrollInstance.dataManager.hasPrevious());
        setHasNextPage(infiniteScrollInstance.dataManager.hasNext());
      }
      const infiniteScrollInstance = useInfiniteScroll({
        // Data
        getPropName: () => data,
        inReverseMode: () => callbackPropsRef.current.reverse,
        shouldFetchNext: () => !callbackPropsRef.current.onlyPrevious,
        shouldFetchPrevious: () => !callbackPropsRef.current.onlyNext,
        shouldPreserveUrl: () => callbackPropsRef.current.preserveUrl,
        // Elements
        getTriggerMargin: () => callbackPropsRef.current.buffer,
        getStartElement: () => resolvedStartElement,
        getEndElement: () => resolvedEndElement,
        getItemsElement: () => resolvedItemsElement,
        getScrollableParent: () => scrollableParent,
        // Callbacks
        onBeforePreviousRequest: () => setLoadingPrevious(true),
        onBeforeNextRequest: () => setLoadingNext(true),
        onCompletePreviousRequest: () => {
          setLoadingPrevious(false);
          syncStateFromDataManager();
        },
        onCompleteNextRequest: () => {
          setLoadingNext(false);
          syncStateFromDataManager();
        },
        onDataReset: syncStateFromDataManager
      });
      setInfiniteScroll(infiniteScrollInstance);
      const { dataManager: dataManager2, elementManager: elementManager2 } = infiniteScrollInstance;
      syncStateFromDataManager();
      elementManager2.setupObservers();
      elementManager2.processServerLoadedElements(dataManager2.getLastLoadedPage());
      if (autoLoad) {
        elementManager2.enableTriggers();
      }
      return () => {
        infiniteScrollInstance.flush();
        setInfiniteScroll(null);
      };
    }, [data, resolvedItemsElement, resolvedStartElement, resolvedEndElement, scrollableParent]);
    const manualMode = useMemo(
      () => manual || manualAfter > 0 && requestCount >= manualAfter,
      [manual, manualAfter, requestCount]
    );
    const autoLoad = useMemo(() => !manualMode, [manualMode]);
    useEffect(() => {
      autoLoad ? elementManager?.enableTriggers() : elementManager?.disableTriggers();
    }, [autoLoad, onlyNext, onlyPrevious, resolvedStartElement, resolvedEndElement]);
    useEffect(() => {
      const shouldAutoScroll = autoScroll !== void 0 ? autoScroll : reverse;
      if (shouldAutoScroll) {
        scrollToBottom();
      }
    }, [scrollableParent]);
    useImperativeHandle(
      ref,
      () => ({
        fetchNext: dataManager?.fetchNext || (() => {
        }),
        fetchPrevious: dataManager?.fetchPrevious || (() => {
        }),
        hasPrevious: dataManager?.hasPrevious || (() => false),
        hasNext: dataManager?.hasNext || (() => false)
      }),
      [dataManager]
    );
    const headerAutoMode = autoLoad && !onlyNext;
    const footerAutoMode = autoLoad && !onlyPrevious;
    const sharedExposed = {
      loadingPrevious,
      loadingNext,
      hasPrevious: hasPreviousPage,
      hasNext: hasNextPage
    };
    const exposedPrevious = {
      loading: loadingPrevious,
      fetch: dataManager?.fetchPrevious ?? (() => {
      }),
      autoMode: headerAutoMode,
      manualMode: !headerAutoMode,
      hasMore: hasPreviousPage,
      ...sharedExposed
    };
    const exposedNext = {
      loading: loadingNext,
      fetch: dataManager?.fetchNext ?? (() => {
      }),
      autoMode: footerAutoMode,
      manualMode: !footerAutoMode,
      hasMore: hasNextPage,
      ...sharedExposed
    };
    const exposedSlot = {
      loading: loadingPrevious || loadingNext,
      loadingPrevious,
      loadingNext
    };
    const renderElements = [];
    if (!startElement) {
      renderElements.push(
        createElement(
          "div",
          { ref: startElementRef },
          // Render previous slot or fallback to loading indicator
          renderSlot(previous, exposedPrevious, loadingPrevious ? renderSlot(loading, exposedPrevious) : null)
        )
      );
    }
    renderElements.push(
      createElement(
        as,
        { ...props, ref: itemsElementRef },
        typeof children === "function" ? children(exposedSlot) : children
      )
    );
    if (!endElement) {
      renderElements.push(
        createElement(
          "div",
          { ref: endElementRef },
          // Render next slot or fallback to loading indicator
          renderSlot(next, exposedNext, loadingNext ? renderSlot(loading, exposedNext) : null)
        )
      );
    }
    return createElement(React2.Fragment, {}, ...reverse ? [...renderElements].reverse() : renderElements);
  }
);
InfiniteScroll.displayName = "InertiaInfiniteScroll";
var noop2 = () => void 0;
var Link = forwardRef(
  ({
    children,
    as = "a",
    data = {},
    href = "",
    method = "get",
    preserveScroll = false,
    preserveState = null,
    preserveUrl = false,
    replace = false,
    only = [],
    except = [],
    headers = {},
    queryStringArrayFormat = "brackets",
    async = false,
    onClick = noop2,
    onCancelToken = noop2,
    onBefore = noop2,
    onStart = noop2,
    onProgress = noop2,
    onFinish = noop2,
    onCancel = noop2,
    onSuccess = noop2,
    onError = noop2,
    onPrefetching = noop2,
    onPrefetched = noop2,
    prefetch = false,
    cacheFor = 0,
    cacheTags = [],
    viewTransition = false,
    ...props
  }, ref) => {
    const [inFlightCount, setInFlightCount] = useState(0);
    const hoverTimeout = useRef(void 0);
    const _method = useMemo(() => {
      return isUrlMethodPair(href) ? href.method : method.toLowerCase();
    }, [href, method]);
    const _as = useMemo(() => {
      if (typeof as !== "string" || as.toLowerCase() !== "a") {
        return as;
      }
      return _method !== "get" ? "button" : as.toLowerCase();
    }, [as, _method]);
    const mergeDataArray = useMemo(
      () => mergeDataIntoQueryString(_method, isUrlMethodPair(href) ? href.url : href, data, queryStringArrayFormat),
      [href, _method, data, queryStringArrayFormat]
    );
    const url = useMemo(() => mergeDataArray[0], [mergeDataArray]);
    const _data = useMemo(() => mergeDataArray[1], [mergeDataArray]);
    const baseParams = useMemo(
      () => ({
        data: _data,
        method: _method,
        preserveScroll,
        preserveState: preserveState ?? _method !== "get",
        preserveUrl,
        replace,
        only,
        except,
        headers,
        async
      }),
      [_data, _method, preserveScroll, preserveState, preserveUrl, replace, only, except, headers, async]
    );
    const visitParams = useMemo(
      () => ({
        ...baseParams,
        viewTransition,
        onCancelToken,
        onBefore,
        onStart(visit) {
          setInFlightCount((count) => count + 1);
          onStart(visit);
        },
        onProgress,
        onFinish(visit) {
          setInFlightCount((count) => count - 1);
          onFinish(visit);
        },
        onCancel,
        onSuccess,
        onError
      }),
      [
        baseParams,
        viewTransition,
        onCancelToken,
        onBefore,
        onStart,
        onProgress,
        onFinish,
        onCancel,
        onSuccess,
        onError
      ]
    );
    const prefetchModes = useMemo(
      () => {
        if (prefetch === true) {
          return ["hover"];
        }
        if (prefetch === false) {
          return [];
        }
        if (Array.isArray(prefetch)) {
          return prefetch;
        }
        return [prefetch];
      },
      Array.isArray(prefetch) ? prefetch : [prefetch]
    );
    const cacheForValue = useMemo(() => {
      if (cacheFor !== 0) {
        return cacheFor;
      }
      if (prefetchModes.length === 1 && prefetchModes[0] === "click") {
        return 0;
      }
      return config.get("prefetch.cacheFor");
    }, [cacheFor, prefetchModes]);
    const doPrefetch = useMemo(() => {
      return () => {
        router.prefetch(
          url,
          {
            ...baseParams,
            onPrefetching,
            onPrefetched
          },
          { cacheFor: cacheForValue, cacheTags }
        );
      };
    }, [url, baseParams, onPrefetching, onPrefetched, cacheForValue, cacheTags]);
    useEffect(() => {
      return () => {
        clearTimeout(hoverTimeout.current);
      };
    }, []);
    useEffect(() => {
      if (prefetchModes.includes("mount")) {
        setTimeout(() => doPrefetch());
      }
    }, prefetchModes);
    const regularEvents = {
      onClick: (event) => {
        onClick(event);
        if (shouldIntercept(event)) {
          event.preventDefault();
          router.visit(url, visitParams);
        }
      }
    };
    const prefetchHoverEvents = {
      onMouseEnter: () => {
        hoverTimeout.current = window.setTimeout(() => {
          doPrefetch();
        }, config.get("prefetch.hoverDelay"));
      },
      onMouseLeave: () => {
        clearTimeout(hoverTimeout.current);
      },
      onClick: regularEvents.onClick
    };
    const prefetchClickEvents = {
      onMouseDown: (event) => {
        if (shouldIntercept(event)) {
          event.preventDefault();
          doPrefetch();
        }
      },
      onKeyDown: (event) => {
        if (shouldNavigate(event)) {
          event.preventDefault();
          doPrefetch();
        }
      },
      onMouseUp: (event) => {
        if (shouldIntercept(event)) {
          event.preventDefault();
          router.visit(url, visitParams);
        }
      },
      onKeyUp: (event) => {
        if (shouldNavigate(event)) {
          event.preventDefault();
          router.visit(url, visitParams);
        }
      },
      onClick: (event) => {
        onClick(event);
        if (shouldIntercept(event)) {
          event.preventDefault();
        }
      }
    };
    const elProps = useMemo(() => {
      if (_as === "button") {
        return { type: "button" };
      }
      if (_as === "a" || typeof _as !== "string") {
        return { href: url };
      }
      return {};
    }, [_as, url]);
    return createElement(
      _as,
      {
        ...props,
        ...elProps,
        ref,
        ...(() => {
          if (prefetchModes.includes("hover")) {
            return prefetchHoverEvents;
          }
          if (prefetchModes.includes("click")) {
            return prefetchClickEvents;
          }
          return regularEvents;
        })(),
        "data-loading": inFlightCount > 0 ? "" : void 0
      },
      children
    );
  }
);
Link.displayName = "InertiaLink";
var Link_default = Link;
var router3 = router;
var config = config$1.extend();
function GuestLayout({ children }) {
  return /* @__PURE__ */ jsxs("div", { className: "flex min-h-screen flex-col items-center bg-gradient-to-br from-lucky-50 via-white to-ticket-50 confetti-bg pt-6 sm:justify-center sm:pt-0", children: [
    /* @__PURE__ */ jsx("div", { className: "absolute top-10 right-20 w-12 h-12 bg-lucky-200 rounded-full opacity-20 animate-float" }),
    /* @__PURE__ */ jsx("div", { className: "absolute bottom-20 left-20 w-8 h-8 bg-ticket-200 rounded-full opacity-20 animate-float", style: { animationDelay: "1s" } }),
    /* @__PURE__ */ jsx("div", { className: "relative z-10", children: /* @__PURE__ */ jsx(Link_default, { href: "/", children: /* @__PURE__ */ jsx(ApplicationLogo, {}) }) }),
    /* @__PURE__ */ jsxs("div", { className: "relative z-10 mt-4 flex items-center gap-4 text-sm", children: [
      /* @__PURE__ */ jsx(
        Link_default,
        {
          href: route("campaigns.index"),
          className: "text-lucky-700 hover:text-lucky-900 font-medium transition-colors",
          children: "Home"
        }
      ),
      /* @__PURE__ */ jsx("span", { className: "text-lucky-300", children: "|" }),
      /* @__PURE__ */ jsx(
        Link_default,
        {
          href: route("login"),
          className: "text-lucky-700 hover:text-lucky-900 font-medium transition-colors",
          children: "Login / Sign up"
        }
      )
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "relative z-10 mt-6 w-full overflow-hidden bg-lucky-50/95 backdrop-blur-sm px-8 py-6 shadow-xl border-2 border-dashed border-lucky-200 sm:max-w-md sm:rounded-2xl", children: [
      /* @__PURE__ */ jsx("div", { className: "absolute -left-3 top-1/2 w-6 h-6 bg-gradient-to-br from-lucky-50 to-ticket-50 rounded-full" }),
      /* @__PURE__ */ jsx("div", { className: "absolute -right-3 top-1/2 w-6 h-6 bg-gradient-to-br from-lucky-50 to-ticket-50 rounded-full" }),
      children
    ] }),
    /* @__PURE__ */ jsx("p", { className: "mt-6 text-sm text-gray-400 relative z-10", children: "🎟️ Your luck starts here!" })
  ] });
}
function ConfirmPassword() {
  const { data, setData, post, processing, errors, reset } = useForm({
    password: ""
  });
  const submit = (e2) => {
    e2.preventDefault();
    post(route("password.confirm"), {
      onFinish: () => reset("password")
    });
  };
  return /* @__PURE__ */ jsxs(GuestLayout, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: "Confirm Password" }),
    /* @__PURE__ */ jsx("div", { className: "mb-4 text-sm text-gray-600", children: "This is a secure area of the application. Please confirm your password before continuing." }),
    /* @__PURE__ */ jsxs("form", { onSubmit: submit, children: [
      /* @__PURE__ */ jsxs("div", { className: "mt-4", children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "password", value: "Password" }),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "password",
            type: "password",
            name: "password",
            value: data.password,
            className: "mt-1 block w-full",
            isFocused: true,
            onChange: (e2) => setData("password", e2.target.value)
          }
        ),
        /* @__PURE__ */ jsx(InputError, { message: errors.password, className: "mt-2" })
      ] }),
      /* @__PURE__ */ jsx("div", { className: "mt-4 flex items-center justify-end", children: /* @__PURE__ */ jsx(PrimaryButton, { className: "ms-4", disabled: processing, children: "Confirm" }) })
    ] })
  ] });
}
const __vite_glob_0_0 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: ConfirmPassword
}, Symbol.toStringTag, { value: "Module" }));
function ForgotPassword({ status }) {
  const { data, setData, post, processing, errors } = useForm({
    email: ""
  });
  const submit = (e2) => {
    e2.preventDefault();
    post(route("password.email"));
  };
  return /* @__PURE__ */ jsxs(GuestLayout, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: "Forgot Password" }),
    /* @__PURE__ */ jsx("div", { className: "mb-4 text-sm text-gray-600", children: "Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one." }),
    status && /* @__PURE__ */ jsx("div", { className: "mb-4 text-sm font-medium text-green-600", children: status }),
    /* @__PURE__ */ jsxs("form", { onSubmit: submit, children: [
      /* @__PURE__ */ jsx(
        TextInput,
        {
          id: "email",
          type: "email",
          name: "email",
          value: data.email,
          className: "mt-1 block w-full",
          isFocused: true,
          onChange: (e2) => setData("email", e2.target.value)
        }
      ),
      /* @__PURE__ */ jsx(InputError, { message: errors.email, className: "mt-2" }),
      /* @__PURE__ */ jsx("div", { className: "mt-4 flex items-center justify-end", children: /* @__PURE__ */ jsx(PrimaryButton, { className: "ms-4", disabled: processing, children: "Email Password Reset Link" }) })
    ] })
  ] });
}
const __vite_glob_0_1 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: ForgotPassword
}, Symbol.toStringTag, { value: "Module" }));
function Checkbox({ className = "", ...props }) {
  return /* @__PURE__ */ jsx(
    "input",
    {
      ...props,
      type: "checkbox",
      className: "rounded border-lucky-300 text-lucky-600 shadow-sm focus:ring-lucky-500 " + className
    }
  );
}
function Login({ status, canResetPassword }) {
  const { data, setData, post, processing, errors, reset } = useForm({
    email: "",
    password: "",
    remember: false
  });
  const submit = (e2) => {
    e2.preventDefault();
    post(route("password-login.store"), {
      onFinish: () => reset("password")
    });
  };
  return /* @__PURE__ */ jsxs(GuestLayout, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: "Log in" }),
    status && /* @__PURE__ */ jsx("div", { className: "mb-4 text-sm font-medium text-green-600", children: status }),
    /* @__PURE__ */ jsxs("form", { onSubmit: submit, children: [
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "email", value: "Email" }),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "email",
            type: "email",
            name: "email",
            value: data.email,
            className: "mt-1 block w-full",
            autoComplete: "username",
            isFocused: true,
            onChange: (e2) => setData("email", e2.target.value)
          }
        ),
        /* @__PURE__ */ jsx(InputError, { message: errors.email, className: "mt-2" })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "mt-4", children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "password", value: "Password" }),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "password",
            type: "password",
            name: "password",
            value: data.password,
            className: "mt-1 block w-full",
            autoComplete: "current-password",
            onChange: (e2) => setData("password", e2.target.value)
          }
        ),
        /* @__PURE__ */ jsx(InputError, { message: errors.password, className: "mt-2" })
      ] }),
      /* @__PURE__ */ jsx("div", { className: "mt-4 block", children: /* @__PURE__ */ jsxs("label", { className: "flex items-center", children: [
        /* @__PURE__ */ jsx(
          Checkbox,
          {
            name: "remember",
            checked: data.remember,
            onChange: (e2) => setData("remember", e2.target.checked)
          }
        ),
        /* @__PURE__ */ jsx("span", { className: "ms-2 text-sm text-gray-600", children: "Remember me" })
      ] }) }),
      /* @__PURE__ */ jsxs("div", { className: "mt-4 flex items-center justify-between", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-1", children: [
          canResetPassword && /* @__PURE__ */ jsx(
            Link_default,
            {
              href: route("password.request"),
              className: "rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-lucky-500 focus:ring-offset-2",
              children: "Forgot your password?"
            }
          ),
          /* @__PURE__ */ jsx(
            Link_default,
            {
              href: route("login"),
              className: "rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-lucky-500 focus:ring-offset-2",
              children: "Login with OTP"
            }
          )
        ] }),
        /* @__PURE__ */ jsx(PrimaryButton, { className: "ms-4", disabled: processing, children: "Log in / Sign up" })
      ] })
    ] })
  ] });
}
const __vite_glob_0_2 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Login
}, Symbol.toStringTag, { value: "Module" }));
function OtpLogin({ status, debugOtp, otpLength = 6 }) {
  const [otpSent, setOtpSent] = useState(false);
  const [otpDigits, setOtpDigits] = useState(Array(otpLength).fill(""));
  const digitRefs = useRef([]);
  const sendForm = useForm({
    identifier: ""
  });
  const verifyForm = useForm({
    identifier: "",
    otp: ""
  });
  useEffect(() => {
    verifyForm.setData("otp", otpDigits.join(""));
  }, [otpDigits]);
  useEffect(() => {
    if (debugOtp && debugOtp.length === otpLength) {
      setOtpDigits(debugOtp.split(""));
    }
  }, [debugOtp]);
  const handleSendOtp = (e2) => {
    e2.preventDefault();
    sendForm.post(route("otp-login.send"), {
      preserveScroll: true,
      onSuccess: (page) => {
        setOtpSent(true);
        setOtpDigits(Array(otpLength).fill(""));
        verifyForm.setData("identifier", sendForm.data.identifier);
        if (page.props.debugOtp) {
          const digits = page.props.debugOtp.split("");
          setOtpDigits(digits);
          verifyForm.setData((prev) => ({
            ...prev,
            identifier: sendForm.data.identifier,
            otp: page.props.debugOtp
          }));
        }
      }
    });
  };
  const handleVerifyOtp = (e2) => {
    e2.preventDefault();
    verifyForm.post(route("otp-login.verify"), {
      onFinish: () => {
        verifyForm.reset("otp");
        setOtpDigits(Array(otpLength).fill(""));
      }
    });
  };
  const handleResendOtp = () => {
    sendForm.post(route("otp-login.send"), {
      preserveScroll: true,
      onSuccess: (page) => {
        setOtpDigits(Array(otpLength).fill(""));
        if (page.props.debugOtp) {
          setOtpDigits(page.props.debugOtp.split(""));
          verifyForm.setData("otp", page.props.debugOtp);
        }
      }
    });
  };
  const handleChangeIdentifier = () => {
    setOtpSent(false);
    setOtpDigits(Array(otpLength).fill(""));
    verifyForm.reset();
    sendForm.reset();
  };
  const handleDigitChange = (index, value) => {
    if (value && !/^\d+$/.test(value)) return;
    const newDigits = [...otpDigits];
    newDigits[index] = value.slice(-1);
    setOtpDigits(newDigits);
    if (value && index < otpLength - 1) {
      digitRefs.current[index + 1]?.focus();
    }
  };
  const handleKeyDown = (index, e2) => {
    if (e2.key === "Backspace" && !otpDigits[index] && index > 0) {
      digitRefs.current[index - 1]?.focus();
    }
  };
  const handlePaste = (e2) => {
    e2.preventDefault();
    const pastedData = e2.clipboardData.getData("text").replace(/\D/g, "").slice(0, otpLength);
    if (pastedData) {
      const newDigits = Array(otpLength).fill("");
      for (let i2 = 0; i2 < pastedData.length; i2++) {
        newDigits[i2] = pastedData[i2];
      }
      setOtpDigits(newDigits);
      const nextFocusIndex = Math.min(pastedData.length, otpLength - 1);
      digitRefs.current[nextFocusIndex]?.focus();
    }
  };
  return /* @__PURE__ */ jsxs(GuestLayout, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: "OTP Login" }),
    status && /* @__PURE__ */ jsx("div", { className: "mb-4 text-sm font-medium text-green-600", children: status }),
    !otpSent ? /* @__PURE__ */ jsxs("form", { onSubmit: handleSendOtp, children: [
      /* @__PURE__ */ jsxs("div", { className: "mb-4 text-center", children: [
        /* @__PURE__ */ jsx("h2", { className: "text-lg font-semibold text-lucky-700", children: "Login with OTP" }),
        /* @__PURE__ */ jsx("p", { className: "mt-1 text-sm text-gray-500", children: "Enter your email or mobile number to receive a one-time password." })
      ] }),
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx(
          InputLabel,
          {
            htmlFor: "identifier",
            value: "Email or Mobile"
          }
        ),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "identifier",
            type: "text",
            name: "identifier",
            value: sendForm.data.identifier,
            className: "mt-1 block w-full",
            isFocused: true,
            placeholder: "email@example.com or 9876543210",
            onChange: (e2) => sendForm.setData("identifier", e2.target.value)
          }
        ),
        /* @__PURE__ */ jsx(
          InputError,
          {
            message: sendForm.errors.identifier,
            className: "mt-2"
          }
        )
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "mt-4 flex items-center justify-between", children: [
        /* @__PURE__ */ jsx("div", { className: "flex flex-col gap-1" }),
        /* @__PURE__ */ jsx(PrimaryButton, { disabled: sendForm.processing, children: "Send OTP" })
      ] })
    ] }) : /* @__PURE__ */ jsxs("form", { onSubmit: handleVerifyOtp, children: [
      /* @__PURE__ */ jsxs("div", { className: "mb-4 text-center", children: [
        /* @__PURE__ */ jsx("h2", { className: "text-lg font-semibold text-lucky-700", children: "Enter OTP" }),
        /* @__PURE__ */ jsxs("p", { className: "mt-1 text-sm text-gray-500", children: [
          "We sent a ",
          otpLength,
          "-digit code to",
          " ",
          /* @__PURE__ */ jsx("span", { className: "font-medium text-lucky-600", children: sendForm.data.identifier })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "otp-0", value: "One-Time Password" }),
        /* @__PURE__ */ jsx("div", { className: "mt-2 flex justify-center gap-2", children: Array.from({ length: otpLength }, (_, index) => /* @__PURE__ */ jsx(
          "input",
          {
            id: `otp-${index}`,
            ref: (el) => digitRefs.current[index] = el,
            type: "text",
            inputMode: "numeric",
            maxLength: 1,
            value: otpDigits[index],
            onChange: (e2) => handleDigitChange(index, e2.target.value),
            onKeyDown: (e2) => handleKeyDown(index, e2),
            onPaste: index === 0 ? handlePaste : void 0,
            autoFocus: index === 0,
            autoComplete: index === 0 ? "one-time-code" : "off",
            className: "h-12 w-12 rounded-lg border border-gray-300 text-center text-xl font-bold shadow-sm focus:border-lucky-500 focus:outline-none focus:ring-2 focus:ring-lucky-500"
          },
          index
        )) }),
        /* @__PURE__ */ jsx(
          InputError,
          {
            message: verifyForm.errors.otp,
            className: "mt-2 text-center"
          }
        ),
        /* @__PURE__ */ jsx(
          InputError,
          {
            message: verifyForm.errors.identifier,
            className: "mt-2 text-center"
          }
        )
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "mt-4 flex items-center justify-between", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-1", children: [
          /* @__PURE__ */ jsx(
            "button",
            {
              type: "button",
              onClick: handleResendOtp,
              disabled: sendForm.processing,
              className: "text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none",
              children: "Resend OTP"
            }
          ),
          /* @__PURE__ */ jsx(
            "button",
            {
              type: "button",
              onClick: handleChangeIdentifier,
              className: "text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none",
              children: "Change email/mobile"
            }
          )
        ] }),
        /* @__PURE__ */ jsx(PrimaryButton, { disabled: verifyForm.processing, children: "Verify & Login" })
      ] })
    ] })
  ] });
}
const __vite_glob_0_3 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: OtpLogin
}, Symbol.toStringTag, { value: "Module" }));
function Register({ status, debugOtp }) {
  const [otpSent, setOtpSent] = useState(false);
  const registerForm = useForm({
    name: "",
    email: "",
    mobile: ""
  });
  const verifyForm = useForm({
    otp: ""
  });
  useEffect(() => {
    if (debugOtp) {
      verifyForm.setData("otp", debugOtp);
      setOtpSent(true);
    }
  }, [debugOtp]);
  const handleSendOtp = (e2) => {
    e2.preventDefault();
    registerForm.post(route("register.send-otp"), {
      preserveScroll: true,
      onSuccess: (page) => {
        setOtpSent(true);
        if (page.props.debugOtp) {
          verifyForm.setData("otp", page.props.debugOtp);
        }
      }
    });
  };
  const handleVerify = (e2) => {
    e2.preventDefault();
    verifyForm.post(route("register.verify"), {
      onFinish: () => verifyForm.reset("otp")
    });
  };
  const handleResendOtp = () => {
    registerForm.post(route("register.send-otp"), {
      preserveScroll: true,
      onSuccess: (page) => {
        if (page.props.debugOtp) {
          verifyForm.setData("otp", page.props.debugOtp);
        }
      }
    });
  };
  const handleBack = () => {
    setOtpSent(false);
    verifyForm.reset();
  };
  return /* @__PURE__ */ jsxs(GuestLayout, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: "Register" }),
    status && /* @__PURE__ */ jsx("div", { className: "mb-4 text-sm font-medium text-green-600", children: status }),
    !otpSent ? /* @__PURE__ */ jsxs("form", { onSubmit: handleSendOtp, children: [
      /* @__PURE__ */ jsxs("div", { className: "mb-4 text-center", children: [
        /* @__PURE__ */ jsx("h2", { className: "text-lg font-semibold text-lucky-700", children: "Create Account" }),
        /* @__PURE__ */ jsx("p", { className: "mt-1 text-sm text-gray-500", children: "We'll verify your mobile number with OTP." })
      ] }),
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "name", value: "Name" }),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "name",
            name: "name",
            value: registerForm.data.name,
            className: "mt-1 block w-full",
            autoComplete: "name",
            isFocused: true,
            onChange: (e2) => registerForm.setData("name", e2.target.value)
          }
        ),
        /* @__PURE__ */ jsx(
          InputError,
          {
            message: registerForm.errors.name,
            className: "mt-2"
          }
        )
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "mt-4", children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "email", value: "Email" }),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "email",
            type: "email",
            name: "email",
            value: registerForm.data.email,
            className: "mt-1 block w-full",
            autoComplete: "username",
            onChange: (e2) => registerForm.setData("email", e2.target.value)
          }
        ),
        /* @__PURE__ */ jsx(
          InputError,
          {
            message: registerForm.errors.email,
            className: "mt-2"
          }
        )
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "mt-4", children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "mobile", value: "Mobile Number" }),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "mobile",
            type: "tel",
            name: "mobile",
            value: registerForm.data.mobile,
            className: "mt-1 block w-full",
            autoComplete: "tel",
            placeholder: "9876543210",
            onChange: (e2) => registerForm.setData("mobile", e2.target.value)
          }
        ),
        /* @__PURE__ */ jsx(
          InputError,
          {
            message: registerForm.errors.mobile,
            className: "mt-2"
          }
        )
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "mt-4 flex items-center justify-between", children: [
        /* @__PURE__ */ jsx(
          Link_default,
          {
            href: route("login"),
            className: "rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-lucky-500 focus:ring-offset-2",
            children: "Already registered?"
          }
        ),
        /* @__PURE__ */ jsx(PrimaryButton, { disabled: registerForm.processing, children: "Send OTP" })
      ] })
    ] }) : /* @__PURE__ */ jsxs("form", { onSubmit: handleVerify, children: [
      /* @__PURE__ */ jsxs("div", { className: "mb-4 text-center", children: [
        /* @__PURE__ */ jsx("h2", { className: "text-lg font-semibold text-lucky-700", children: "Verify Mobile" }),
        /* @__PURE__ */ jsxs("p", { className: "mt-1 text-sm text-gray-500", children: [
          "Enter the 6-digit code sent to",
          " ",
          /* @__PURE__ */ jsx("span", { className: "font-medium text-lucky-600", children: registerForm.data.mobile })
        ] })
      ] }),
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "otp", value: "One-Time Password" }),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "otp",
            type: "text",
            name: "otp",
            value: verifyForm.data.otp,
            className: "mt-1 block w-full text-center text-2xl tracking-[0.5em]",
            isFocused: true,
            maxLength: 6,
            placeholder: "000000",
            autoComplete: "one-time-code",
            onChange: (e2) => verifyForm.setData(
              "otp",
              e2.target.value.replace(/\D/g, "")
            )
          }
        ),
        /* @__PURE__ */ jsx(
          InputError,
          {
            message: verifyForm.errors.otp,
            className: "mt-2"
          }
        )
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "mt-4 flex items-center justify-between", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col gap-1", children: [
          /* @__PURE__ */ jsx(
            "button",
            {
              type: "button",
              onClick: handleResendOtp,
              disabled: registerForm.processing,
              className: "text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none",
              children: "Resend OTP"
            }
          ),
          /* @__PURE__ */ jsx(
            "button",
            {
              type: "button",
              onClick: handleBack,
              className: "text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none",
              children: "Edit details"
            }
          )
        ] }),
        /* @__PURE__ */ jsx(PrimaryButton, { disabled: verifyForm.processing, children: "Verify & Register" })
      ] })
    ] })
  ] });
}
const __vite_glob_0_4 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Register
}, Symbol.toStringTag, { value: "Module" }));
function ResetPassword({ token, email }) {
  const { data, setData, post, processing, errors, reset } = useForm({
    token,
    email,
    password: "",
    password_confirmation: ""
  });
  const submit = (e2) => {
    e2.preventDefault();
    post(route("password.store"), {
      onFinish: () => reset("password", "password_confirmation")
    });
  };
  return /* @__PURE__ */ jsxs(GuestLayout, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: "Reset Password" }),
    /* @__PURE__ */ jsxs("form", { onSubmit: submit, children: [
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "email", value: "Email" }),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "email",
            type: "email",
            name: "email",
            value: data.email,
            className: "mt-1 block w-full",
            autoComplete: "username",
            onChange: (e2) => setData("email", e2.target.value)
          }
        ),
        /* @__PURE__ */ jsx(InputError, { message: errors.email, className: "mt-2" })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "mt-4", children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "password", value: "Password" }),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "password",
            type: "password",
            name: "password",
            value: data.password,
            className: "mt-1 block w-full",
            autoComplete: "new-password",
            isFocused: true,
            onChange: (e2) => setData("password", e2.target.value)
          }
        ),
        /* @__PURE__ */ jsx(InputError, { message: errors.password, className: "mt-2" })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "mt-4", children: [
        /* @__PURE__ */ jsx(
          InputLabel,
          {
            htmlFor: "password_confirmation",
            value: "Confirm Password"
          }
        ),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            type: "password",
            id: "password_confirmation",
            name: "password_confirmation",
            value: data.password_confirmation,
            className: "mt-1 block w-full",
            autoComplete: "new-password",
            onChange: (e2) => setData("password_confirmation", e2.target.value)
          }
        ),
        /* @__PURE__ */ jsx(
          InputError,
          {
            message: errors.password_confirmation,
            className: "mt-2"
          }
        )
      ] }),
      /* @__PURE__ */ jsx("div", { className: "mt-4 flex items-center justify-end", children: /* @__PURE__ */ jsx(PrimaryButton, { className: "ms-4", disabled: processing, children: "Reset Password" }) })
    ] })
  ] });
}
const __vite_glob_0_5 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: ResetPassword
}, Symbol.toStringTag, { value: "Module" }));
function VerifyEmail({ status }) {
  const { post, processing } = useForm({});
  const submit = (e2) => {
    e2.preventDefault();
    post(route("verification.send"));
  };
  return /* @__PURE__ */ jsxs(GuestLayout, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: "Email Verification" }),
    /* @__PURE__ */ jsx("div", { className: "mb-4 text-sm text-gray-600", children: "Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another." }),
    status === "verification-link-sent" && /* @__PURE__ */ jsx("div", { className: "mb-4 text-sm font-medium text-green-600", children: "A new verification link has been sent to the email address you provided during registration." }),
    /* @__PURE__ */ jsx("form", { onSubmit: submit, children: /* @__PURE__ */ jsxs("div", { className: "mt-4 flex items-center justify-between", children: [
      /* @__PURE__ */ jsx(PrimaryButton, { disabled: processing, children: "Resend Verification Email" }),
      /* @__PURE__ */ jsx(
        Link_default,
        {
          href: route("logout"),
          method: "post",
          as: "button",
          className: "rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2",
          children: "Log Out"
        }
      )
    ] }) })
  ] });
}
const __vite_glob_0_6 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: VerifyEmail
}, Symbol.toStringTag, { value: "Module" }));
const DropDownContext = createContext();
const Dropdown = ({ children }) => {
  const [open, setOpen] = useState(false);
  const toggleOpen = () => {
    setOpen((previousState) => !previousState);
  };
  return /* @__PURE__ */ jsx(DropDownContext.Provider, { value: { open, setOpen, toggleOpen }, children: /* @__PURE__ */ jsx("div", { className: "relative", children }) });
};
const Trigger = ({ children }) => {
  const { open, setOpen, toggleOpen } = useContext(DropDownContext);
  return /* @__PURE__ */ jsxs(Fragment$1, { children: [
    /* @__PURE__ */ jsx("div", { onClick: toggleOpen, children }),
    open && /* @__PURE__ */ jsx(
      "div",
      {
        className: "fixed inset-0 z-40",
        onClick: () => setOpen(false)
      }
    )
  ] });
};
const Content = ({
  align = "right",
  width = "48",
  contentClasses = "py-1 bg-white",
  children
}) => {
  const { open, setOpen } = useContext(DropDownContext);
  let alignmentClasses = "origin-top";
  if (align === "left") {
    alignmentClasses = "ltr:origin-top-left rtl:origin-top-right start-0";
  } else if (align === "right") {
    alignmentClasses = "ltr:origin-top-right rtl:origin-top-left end-0";
  }
  let widthClasses = "";
  if (width === "48") {
    widthClasses = "w-48";
  }
  return /* @__PURE__ */ jsx(Fragment$1, { children: /* @__PURE__ */ jsx(
    Transition,
    {
      show: open,
      enter: "transition ease-out duration-200",
      enterFrom: "opacity-0 scale-95",
      enterTo: "opacity-100 scale-100",
      leave: "transition ease-in duration-75",
      leaveFrom: "opacity-100 scale-100",
      leaveTo: "opacity-0 scale-95",
      children: /* @__PURE__ */ jsx(
        "div",
        {
          className: `absolute z-50 mt-2 rounded-md shadow-lg ${alignmentClasses} ${widthClasses}`,
          onClick: () => setOpen(false),
          children: /* @__PURE__ */ jsx(
            "div",
            {
              className: `rounded-md ring-1 ring-black ring-opacity-5 ` + contentClasses,
              children
            }
          )
        }
      )
    }
  ) });
};
const DropdownLink = ({ className = "", children, ...props }) => {
  return /* @__PURE__ */ jsx(
    Link_default,
    {
      ...props,
      className: "block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none " + className,
      children
    }
  );
};
Dropdown.Trigger = Trigger;
Dropdown.Content = Content;
Dropdown.Link = DropdownLink;
function NavLink({
  active = false,
  className = "",
  children,
  ...props
}) {
  return /* @__PURE__ */ jsx(
    Link_default,
    {
      ...props,
      className: "inline-flex items-center border-b-3 px-1 pt-1 text-sm font-bold leading-5 transition duration-150 ease-in-out focus:outline-none " + (active ? "border-lucky-500 text-lucky-700 focus:border-lucky-700" : "border-transparent text-gray-500 hover:border-lucky-300 hover:text-lucky-600 focus:border-lucky-300 focus:text-lucky-600") + " " + className,
      children
    }
  );
}
function ResponsiveNavLink({
  active = false,
  className = "",
  children,
  ...props
}) {
  return /* @__PURE__ */ jsx(
    Link_default,
    {
      ...props,
      className: `flex w-full items-start border-l-4 py-2 pe-4 ps-3 ${active ? "border-lucky-500 bg-lucky-50 text-lucky-700 focus:border-lucky-700 focus:bg-lucky-100 focus:text-lucky-800" : "border-transparent text-gray-600 hover:border-lucky-300 hover:bg-lucky-50 hover:text-lucky-700 focus:border-lucky-300 focus:bg-lucky-50 focus:text-lucky-700"} text-base font-bold transition duration-150 ease-in-out focus:outline-none ${className}`,
      children
    }
  );
}
function AuthenticatedLayout({ header, children }) {
  const user = usePage().props.auth.user;
  const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);
  const mobileMenuRef = useRef(null);
  useEffect(() => {
    setShowingNavigationDropdown(false);
  }, [children]);
  return /* @__PURE__ */ jsxs("div", { className: "min-h-screen bg-gradient-to-br from-lucky-50 via-white to-ticket-50 confetti-bg", children: [
    /* @__PURE__ */ jsxs("nav", { className: "sticky top-0 z-50 border-b-2 border-lucky-200 bg-white/95 backdrop-blur-md shadow-sm", children: [
      /* @__PURE__ */ jsx("div", { className: "mx-auto max-w-7xl px-4 sm:px-6 lg:px-8", children: /* @__PURE__ */ jsxs("div", { className: "flex h-16 justify-between", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex", children: [
          /* @__PURE__ */ jsx("div", { className: "flex shrink-0 items-center", children: /* @__PURE__ */ jsx(Link_default, { href: "/", children: /* @__PURE__ */ jsx(ApplicationLogo, {}) }) }),
          /* @__PURE__ */ jsxs("div", { className: "hidden space-x-6 sm:-my-px sm:ms-10 sm:flex", children: [
            user && /* @__PURE__ */ jsx(NavLink, { href: route("dashboard"), active: route().current("dashboard"), children: "🎯 Dashboard" }),
            user && /* @__PURE__ */ jsx(NavLink, { href: route("transactions.index"), active: route().current("transactions.*"), children: "💳 Transactions" }),
            /* @__PURE__ */ jsx(NavLink, { href: route("campaigns.index"), active: route().current("campaigns.*"), children: "🏆 Campaigns" }),
            /* @__PURE__ */ jsx(NavLink, { href: route("coupons.index"), active: route().current("coupons.*"), children: "🎫 Coupons" }),
            /* @__PURE__ */ jsx(NavLink, { href: route("subscriptions.index"), active: route().current("subscriptions.*"), children: "⭐ Plans" })
          ] })
        ] }),
        /* @__PURE__ */ jsx("div", { className: "hidden sm:ms-6 sm:flex sm:items-center", children: /* @__PURE__ */ jsx("div", { className: "relative ms-3", children: user ? /* @__PURE__ */ jsxs(Dropdown, { children: [
          /* @__PURE__ */ jsx(Dropdown.Trigger, { children: /* @__PURE__ */ jsx("span", { className: "inline-flex rounded-full", children: /* @__PURE__ */ jsxs(
            "button",
            {
              type: "button",
              className: "inline-flex items-center gap-2 rounded-full border-2 border-lucky-200 bg-lucky-50 px-4 py-2 text-sm font-bold text-lucky-700 transition duration-150 ease-in-out hover:bg-lucky-100 focus:outline-none",
              children: [
                /* @__PURE__ */ jsx("span", { className: "w-6 h-6 rounded-full bg-gradient-to-br from-lucky-400 to-ticket-400 flex items-center justify-center text-white text-xs font-bold", children: user.name.charAt(0).toUpperCase() }),
                user.name,
                /* @__PURE__ */ jsx(
                  "svg",
                  {
                    className: "-me-0.5 ms-1 h-4 w-4 text-lucky-400",
                    xmlns: "http://www.w3.org/2000/svg",
                    viewBox: "0 0 20 20",
                    fill: "currentColor",
                    children: /* @__PURE__ */ jsx(
                      "path",
                      {
                        fillRule: "evenodd",
                        d: "M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z",
                        clipRule: "evenodd"
                      }
                    )
                  }
                )
              ]
            }
          ) }) }),
          /* @__PURE__ */ jsxs(Dropdown.Content, { children: [
            /* @__PURE__ */ jsx(
              Dropdown.Link,
              {
                href: route("profile.edit"),
                children: "👤 Profile"
              }
            ),
            /* @__PURE__ */ jsx(
              Dropdown.Link,
              {
                href: route("logout"),
                method: "post",
                as: "button",
                children: "🚪 Log Out"
              }
            )
          ] })
        ] }) : /* @__PURE__ */ jsx("div", { className: "flex gap-3", children: /* @__PURE__ */ jsx(
          Link_default,
          {
            href: route("login"),
            className: "rounded-full px-4 py-2 text-sm font-bold text-lucky-700 border-2 border-lucky-300 hover:bg-lucky-50 transition-colors",
            children: "Login / Register"
          }
        ) }) }) }),
        /* @__PURE__ */ jsx("div", { className: "-me-2 flex items-center sm:hidden", children: /* @__PURE__ */ jsx(
          "button",
          {
            onClick: () => setShowingNavigationDropdown(
              (previousState) => !previousState
            ),
            className: "inline-flex items-center justify-center rounded-full p-2 text-lucky-500 transition duration-150 ease-in-out hover:bg-lucky-50 hover:text-lucky-700 focus:bg-lucky-50 focus:text-lucky-700 focus:outline-none",
            children: /* @__PURE__ */ jsxs(
              "svg",
              {
                className: "h-6 w-6",
                stroke: "currentColor",
                fill: "none",
                viewBox: "0 0 24 24",
                children: [
                  /* @__PURE__ */ jsx(
                    "path",
                    {
                      className: !showingNavigationDropdown ? "inline-flex" : "hidden",
                      strokeLinecap: "round",
                      strokeLinejoin: "round",
                      strokeWidth: "2",
                      d: "M4 6h16M4 12h16M4 18h16"
                    }
                  ),
                  /* @__PURE__ */ jsx(
                    "path",
                    {
                      className: showingNavigationDropdown ? "inline-flex" : "hidden",
                      strokeLinecap: "round",
                      strokeLinejoin: "round",
                      strokeWidth: "2",
                      d: "M6 18L18 6M6 6l12 12"
                    }
                  )
                ]
              }
            )
          }
        ) })
      ] }) }),
      /* @__PURE__ */ jsxs(
        "div",
        {
          ref: mobileMenuRef,
          className: `sm:hidden overflow-hidden transition-all duration-300 ease-in-out ${showingNavigationDropdown ? "max-h-[500px] opacity-100" : "max-h-0 opacity-0"}`,
          children: [
            /* @__PURE__ */ jsxs("div", { className: "space-y-1 pb-3 pt-2", children: [
              user && /* @__PURE__ */ jsx(
                ResponsiveNavLink,
                {
                  href: route("dashboard"),
                  active: route().current("dashboard"),
                  children: "🎯 Dashboard"
                }
              ),
              user && /* @__PURE__ */ jsx(
                ResponsiveNavLink,
                {
                  href: route("transactions.index"),
                  active: route().current("transactions.*"),
                  children: "💳 Transactions"
                }
              ),
              /* @__PURE__ */ jsx(
                ResponsiveNavLink,
                {
                  href: route("campaigns.index"),
                  active: route().current("campaigns.*"),
                  children: "🏆 Campaigns"
                }
              ),
              /* @__PURE__ */ jsx(
                ResponsiveNavLink,
                {
                  href: route("coupons.index"),
                  active: route().current("coupons.*"),
                  children: "🎫 Coupons"
                }
              ),
              /* @__PURE__ */ jsx(
                ResponsiveNavLink,
                {
                  href: route("subscriptions.index"),
                  active: route().current("subscriptions.*"),
                  children: "⭐ Plans"
                }
              )
            ] }),
            user ? /* @__PURE__ */ jsxs("div", { className: "border-t border-lucky-200 pb-1 pt-4", children: [
              /* @__PURE__ */ jsxs("div", { className: "px-4 flex items-center gap-3", children: [
                /* @__PURE__ */ jsx("span", { className: "w-8 h-8 rounded-full bg-gradient-to-br from-lucky-400 to-ticket-400 flex items-center justify-center text-white text-sm font-bold", children: user.name.charAt(0).toUpperCase() }),
                /* @__PURE__ */ jsxs("div", { children: [
                  /* @__PURE__ */ jsx("div", { className: "text-base font-bold text-gray-800", children: user.name }),
                  /* @__PURE__ */ jsx("div", { className: "text-sm text-gray-500", children: user.email })
                ] })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "mt-3 space-y-1", children: [
                /* @__PURE__ */ jsx(ResponsiveNavLink, { href: route("profile.edit"), children: "👤 Profile" }),
                /* @__PURE__ */ jsx(
                  ResponsiveNavLink,
                  {
                    method: "post",
                    href: route("logout"),
                    as: "button",
                    children: "🚪 Log Out"
                  }
                )
              ] })
            ] }) : /* @__PURE__ */ jsx("div", { className: "border-t border-lucky-200 pb-1 pt-4", children: /* @__PURE__ */ jsx("div", { className: "mt-3 space-y-1", children: /* @__PURE__ */ jsx(ResponsiveNavLink, { href: route("login"), children: "Log in / Sign up" }) }) })
          ]
        }
      )
    ] }),
    header && /* @__PURE__ */ jsx("header", { className: "bg-gradient-to-r from-lucky-500 via-lucky-400 to-ticket-400 shadow-lg", children: /* @__PURE__ */ jsx("div", { className: "mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8", children: /* @__PURE__ */ jsx("div", { className: "text-white font-display", children: header }) }) }),
    /* @__PURE__ */ jsx("main", { children })
  ] });
}
function BountyMeter({ percentage = 0, size = "md" }) {
  const clamped = Math.min(Math.max(percentage, 0), 100);
  const sizeClasses = {
    sm: { bar: "h-2", text: "text-xs", wrapper: "" },
    md: { bar: "h-4", text: "text-xs", wrapper: "" },
    lg: { bar: "h-5", text: "text-sm", wrapper: "" }
  };
  const s2 = sizeClasses[size] || sizeClasses.md;
  const meterColor = clamped >= 80 ? "from-green-400 to-emerald-500" : clamped >= 50 ? "from-lucky-400 to-lucky-600" : clamped >= 25 ? "from-yellow-400 to-amber-500" : "from-orange-400 to-red-400";
  return /* @__PURE__ */ jsxs("div", { className: s2.wrapper, children: [
    /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mb-1", children: [
      /* @__PURE__ */ jsx("span", { className: `font-bold text-lucky-700 ${s2.text}`, children: "🔥 Bounty" }),
      /* @__PURE__ */ jsxs("span", { className: `font-bold text-lucky-600 ${s2.text}`, children: [
        clamped,
        "%"
      ] })
    ] }),
    /* @__PURE__ */ jsx("div", { className: `overflow-hidden ${s2.bar} rounded-full bg-lucky-100 border border-lucky-200`, children: /* @__PURE__ */ jsx(
      "div",
      {
        style: { width: `${clamped}%` },
        className: `h-full rounded-full bg-gradient-to-r ${meterColor} transition-all duration-700 ease-out`
      }
    ) })
  ] });
}
function Index$2({ auth, campaigns }) {
  return /* @__PURE__ */ jsxs(
    AuthenticatedLayout,
    {
      user: auth.user,
      header: /* @__PURE__ */ jsx("h2", { className: "text-xl font-bold leading-tight text-white flex items-center gap-2", children: "🏆 Campaigns" }),
      children: [
        /* @__PURE__ */ jsx(Head_default, { title: "Campaigns" }),
        /* @__PURE__ */ jsx("div", { className: "py-8", children: /* @__PURE__ */ jsx("div", { className: "max-w-7xl mx-auto sm:px-6 lg:px-8", children: /* @__PURE__ */ jsx("div", { className: "grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6", children: campaigns.data.length > 0 ? campaigns.data.map((campaign) => /* @__PURE__ */ jsx(
          Link_default,
          {
            href: route("campaigns.show", campaign.id),
            className: "block group",
            children: /* @__PURE__ */ jsxs("div", { className: "coupon-card overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1", children: [
              /* @__PURE__ */ jsxs("div", { className: "h-48 bg-gradient-to-br from-lucky-100 to-ticket-100 w-full relative overflow-hidden", children: [
                /* @__PURE__ */ jsx(
                  "img",
                  {
                    src: campaign.creator?.merchant?.logo || `https://placehold.co/600x400?text=${encodeURIComponent(campaign.reward_name)}`,
                    alt: campaign.reward_name,
                    className: "w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  }
                ),
                /* @__PURE__ */ jsx("div", { className: "absolute top-3 right-3 golden-badge px-3 py-1 rounded-full text-xs shadow-md", children: campaign.category?.name })
              ] }),
              /* @__PURE__ */ jsx("div", { className: "flex justify-center gap-2 py-1.5 bg-gradient-to-r from-transparent via-lucky-50 to-transparent", children: [...Array(10)].map((_, i2) => /* @__PURE__ */ jsx("div", { className: "w-2 h-2 rounded-full bg-lucky-200" }, i2)) }),
              /* @__PURE__ */ jsxs("div", { className: "p-5", children: [
                /* @__PURE__ */ jsx("div", { className: "flex items-center justify-between mb-2", children: /* @__PURE__ */ jsx("span", { className: "text-xs font-bold text-lucky-600 uppercase tracking-wider", children: campaign.creator?.merchant?.name || "Kutoot Exclusive" }) }),
                /* @__PURE__ */ jsx("h3", { className: "font-display text-lg text-gray-900 mb-2 truncate", children: campaign.reward_name }),
                /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 mb-4 line-clamp-2", children: "Collect stamps to unlock this reward." }),
                /* @__PURE__ */ jsx("div", { className: "mb-4", children: /* @__PURE__ */ jsx(BountyMeter, { percentage: campaign.bounty_percentage, size: "sm" }) }),
                /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between", children: [
                  /* @__PURE__ */ jsxs("div", { className: "flex items-center text-sm text-lucky-600 font-bold", children: [
                    /* @__PURE__ */ jsx("span", { className: "text-lg mr-1", children: "🎫" }),
                    /* @__PURE__ */ jsxs("span", { children: [
                      "Target: ",
                      campaign.stamp_target,
                      " Stamps"
                    ] })
                  ] }),
                  /* @__PURE__ */ jsx("span", { className: "text-lucky-500 group-hover:translate-x-1 transition-transform", children: "→" })
                ] })
              ] })
            ] })
          },
          campaign.id
        )) : /* @__PURE__ */ jsxs("div", { className: "col-span-full text-center py-16", children: [
          /* @__PURE__ */ jsx("span", { className: "text-5xl mb-4 block", children: "🎭" }),
          /* @__PURE__ */ jsx("h3", { className: "font-display text-lg text-gray-900", children: "No campaigns available" }),
          /* @__PURE__ */ jsx("p", { className: "mt-1 text-sm text-gray-500", children: "Check back later for new rewards." })
        ] }) }) }) })
      ]
    }
  );
}
const __vite_glob_0_7 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Index$2
}, Symbol.toStringTag, { value: "Module" }));
function CurrencySymbol() {
  const { currency } = usePage().props;
  const symbols = {
    "INR": "₹",
    "USD": "$",
    "EUR": "€",
    "GBP": "£"
  };
  return /* @__PURE__ */ jsx(Fragment$1, { children: symbols[currency] || currency });
}
function Show({ auth, campaign, bountyMeter, collectedCommission, issuedStamps }) {
  const progressPercentage = Math.min(Math.round(bountyMeter * 100), 100);
  const isLoggedIn = !!auth.user;
  return /* @__PURE__ */ jsxs(
    AuthenticatedLayout,
    {
      user: auth.user,
      header: /* @__PURE__ */ jsx("h2", { className: "text-xl font-bold leading-tight text-white flex items-center gap-2", children: "🏆 Campaign Details" }),
      children: [
        /* @__PURE__ */ jsx(Head_default, { title: campaign.reward_name }),
        /* @__PURE__ */ jsx("div", { className: "py-8", children: /* @__PURE__ */ jsx("div", { className: "max-w-7xl mx-auto sm:px-6 lg:px-8", children: /* @__PURE__ */ jsx("div", { className: "coupon-card overflow-hidden", children: /* @__PURE__ */ jsxs("div", { className: "md:flex", children: [
          /* @__PURE__ */ jsx("div", { className: "md:flex-shrink-0", children: /* @__PURE__ */ jsx(
            "img",
            {
              className: "h-48 w-full object-cover md:w-48",
              src: campaign.creator?.merchant?.logo || `https://placehold.co/400x400?text=${encodeURIComponent(campaign.reward_name)}`,
              alt: campaign.reward_name
            }
          ) }),
          /* @__PURE__ */ jsxs("div", { className: "p-8 w-full", children: [
            /* @__PURE__ */ jsx("div", { className: "uppercase tracking-wide text-sm text-lucky-600 font-bold", children: campaign.category?.name }),
            /* @__PURE__ */ jsx("h1", { className: "block mt-1 text-lg leading-tight font-display text-gray-900", children: campaign.reward_name }),
            /* @__PURE__ */ jsx("p", { className: "mt-2 text-gray-500", children: campaign.description || "Complete the requirements to unlock this reward!" }),
            /* @__PURE__ */ jsxs("div", { className: "mt-6", children: [
              /* @__PURE__ */ jsxs("h3", { className: "text-lg font-display text-gray-900 flex items-center gap-2", children: [
                /* @__PURE__ */ jsx("span", { children: "📊" }),
                " Progress"
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "relative pt-1", children: [
                /* @__PURE__ */ jsxs("div", { className: "flex mb-2 items-center justify-between", children: [
                  /* @__PURE__ */ jsx("div", { children: /* @__PURE__ */ jsx("span", { className: "text-xs font-bold inline-block py-1 px-3 uppercase rounded-full text-lucky-700 bg-lucky-100 border border-lucky-200", children: "Bounty Meter" }) }),
                  /* @__PURE__ */ jsx("div", { className: "text-right", children: /* @__PURE__ */ jsxs("span", { className: "text-xs font-bold inline-block text-lucky-600", children: [
                    progressPercentage,
                    "%"
                  ] }) })
                ] }),
                /* @__PURE__ */ jsx("div", { className: "overflow-hidden h-4 mb-4 text-xs flex rounded-full bg-lucky-100 border border-lucky-200", children: /* @__PURE__ */ jsx(
                  "div",
                  {
                    style: { width: `${progressPercentage}%` },
                    className: "shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center lucky-gradient transition-all duration-500 ease-out rounded-full"
                  }
                ) })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 gap-4 mt-4 text-sm", children: [
                /* @__PURE__ */ jsxs("div", { className: "bg-gradient-to-br from-lucky-50 to-lucky-100 p-4 rounded-xl border border-dashed border-lucky-200", children: [
                  /* @__PURE__ */ jsx("span", { className: "block font-display text-lucky-700", children: "💰 Review Spend" }),
                  /* @__PURE__ */ jsxs("span", { className: "block text-gray-700", children: [
                    "Collected: ",
                    /* @__PURE__ */ jsxs("span", { className: "font-bold text-lucky-600", children: [
                      /* @__PURE__ */ jsx(CurrencySymbol, {}),
                      parseFloat(collectedCommission).toFixed(2)
                    ] })
                  ] }),
                  /* @__PURE__ */ jsxs("span", { className: "block text-xs text-gray-400", children: [
                    "Target: ",
                    /* @__PURE__ */ jsx(CurrencySymbol, {}),
                    parseFloat(campaign.reward_cost_target).toFixed(2)
                  ] })
                ] }),
                /* @__PURE__ */ jsxs("div", { className: "bg-gradient-to-br from-ticket-50 to-ticket-100 p-4 rounded-xl border border-dashed border-ticket-200", children: [
                  /* @__PURE__ */ jsx("span", { className: "block font-display text-ticket-700", children: "🎫 Stamps" }),
                  /* @__PURE__ */ jsxs("span", { className: "block text-gray-700", children: [
                    "Collected: ",
                    /* @__PURE__ */ jsx("span", { className: "font-bold text-ticket-600", children: issuedStamps })
                  ] }),
                  /* @__PURE__ */ jsxs("span", { className: "block text-xs text-gray-400", children: [
                    "Target: ",
                    campaign.stamp_target
                  ] })
                ] })
              ] }),
              campaign.stamp_config && /* @__PURE__ */ jsxs("div", { className: "mt-6 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-4 border border-dashed border-purple-200", children: [
                /* @__PURE__ */ jsx("h4", { className: "text-sm font-display text-purple-700 mb-3 flex items-center gap-2", children: "🎰 Stamp Code Format" }),
                /* @__PURE__ */ jsxs("div", { className: "space-y-2", children: [
                  /* @__PURE__ */ jsxs("div", { className: "bg-white/60 rounded-lg p-3 text-center", children: [
                    /* @__PURE__ */ jsx("p", { className: "text-xs text-purple-500 font-medium mb-1", children: "Sample Code" }),
                    /* @__PURE__ */ jsx("p", { className: "font-mono text-lg font-bold text-purple-700", children: campaign.stamp_config.sample_code })
                  ] }),
                  /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 gap-2 text-xs", children: [
                    /* @__PURE__ */ jsxs("div", { className: "bg-white/60 rounded-lg p-2", children: [
                      /* @__PURE__ */ jsx("span", { className: "text-purple-500", children: "Slots:" }),
                      /* @__PURE__ */ jsx("span", { className: "ml-1 font-bold text-purple-700", children: campaign.stamp_config.slots })
                    ] }),
                    /* @__PURE__ */ jsxs("div", { className: "bg-white/60 rounded-lg p-2", children: [
                      /* @__PURE__ */ jsx("span", { className: "text-purple-500", children: "Range:" }),
                      /* @__PURE__ */ jsxs("span", { className: "ml-1 font-bold text-purple-700", children: [
                        campaign.stamp_config.min,
                        " – ",
                        campaign.stamp_config.max
                      ] })
                    ] })
                  ] }),
                  /* @__PURE__ */ jsxs("div", { className: "bg-white/60 rounded-lg p-2 text-xs text-center", children: [
                    /* @__PURE__ */ jsx("span", { className: "text-purple-500", children: "Possible Combinations:" }),
                    /* @__PURE__ */ jsx("span", { className: "ml-1 font-bold text-purple-700", children: campaign.stamp_config.possible_combinations })
                  ] }),
                  /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap gap-2 mt-1", children: [
                    campaign.stamp_config.editable_on_plan_purchase && /* @__PURE__ */ jsx("span", { className: "inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-ticket-100 text-ticket-700", children: "⭐ Editable on Plan Purchase" }),
                    campaign.stamp_config.editable_on_coupon_redemption && /* @__PURE__ */ jsx("span", { className: "inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700", children: "🎟️ Editable on Coupon Redemption" })
                  ] })
                ] })
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "mt-6 flex flex-wrap gap-3", children: [
              /* @__PURE__ */ jsxs(
                "button",
                {
                  disabled: progressPercentage < 100,
                  className: `inline-flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-bold shadow-md transition-all ${progressPercentage >= 100 ? "lucky-gradient text-white hover:shadow-lg transform hover:-translate-y-0.5 animate-pulse-glow" : "bg-gray-200 text-gray-500 cursor-not-allowed"}`,
                  children: [
                    /* @__PURE__ */ jsx("span", { children: progressPercentage >= 100 ? "🎁" : "⏳" }),
                    progressPercentage >= 100 ? "Claim Reward" : "In Progress"
                  ]
                }
              ),
              !isLoggedIn ? /* @__PURE__ */ jsx(
                Link_default,
                {
                  href: route("login"),
                  className: "inline-flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-bold border-2 border-lucky-300 text-lucky-700 bg-white hover:bg-lucky-50 transition-colors",
                  children: "🔑 Login to Set Primary"
                }
              ) : auth.user.primary_campaign_id !== campaign.id ? /* @__PURE__ */ jsx(
                "button",
                {
                  onClick: () => {
                    if (confirm("Set this as your primary campaign for future stamps?")) {
                      router3.patch(route("profile.update-primary-campaign"), {
                        primary_campaign_id: campaign.id
                      });
                    }
                  },
                  className: "inline-flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-bold border-2 border-lucky-300 text-lucky-700 bg-white hover:bg-lucky-50 transition-colors",
                  children: "🎯 Set as Primary"
                }
              ) : /* @__PURE__ */ jsx("span", { className: "inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold golden-badge", children: "⭐ Primary Campaign" })
            ] })
          ] })
        ] }) }) }) })
      ]
    }
  );
}
const __vite_glob_0_8 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Show
}, Symbol.toStringTag, { value: "Module" }));
function Modal({
  children,
  show = false,
  maxWidth = "2xl",
  closeable = true,
  onClose = () => {
  }
}) {
  const close = () => {
    if (closeable) {
      onClose();
    }
  };
  const maxWidthClass = {
    sm: "sm:max-w-sm",
    md: "sm:max-w-md",
    lg: "sm:max-w-lg",
    xl: "sm:max-w-xl",
    "2xl": "sm:max-w-2xl"
  }[maxWidth];
  return /* @__PURE__ */ jsx(Transition, { show, leave: "duration-200", children: /* @__PURE__ */ jsxs(
    Dialog,
    {
      as: "div",
      id: "modal",
      className: "fixed inset-0 z-50 flex transform items-center overflow-y-auto px-4 py-6 transition-all sm:px-0",
      onClose: close,
      children: [
        /* @__PURE__ */ jsx(
          TransitionChild,
          {
            enter: "ease-out duration-300",
            enterFrom: "opacity-0",
            enterTo: "opacity-100",
            leave: "ease-in duration-200",
            leaveFrom: "opacity-100",
            leaveTo: "opacity-0",
            children: /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-gray-500/75" })
          }
        ),
        /* @__PURE__ */ jsx(
          TransitionChild,
          {
            enter: "ease-out duration-300",
            enterFrom: "opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95",
            enterTo: "opacity-100 translate-y-0 sm:scale-100",
            leave: "ease-in duration-200",
            leaveFrom: "opacity-100 translate-y-0 sm:scale-100",
            leaveTo: "opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95",
            children: /* @__PURE__ */ jsx(
              DialogPanel,
              {
                className: `mb-6 transform overflow-hidden rounded-lg bg-white shadow-xl transition-all sm:mx-auto sm:w-full ${maxWidthClass}`,
                children
              }
            )
          }
        )
      ]
    }
  ) });
}
function SecondaryButton({
  type = "button",
  className = "",
  disabled,
  children,
  ...props
}) {
  return /* @__PURE__ */ jsx(
    "button",
    {
      ...props,
      type,
      className: `inline-flex items-center rounded-full border-2 border-lucky-300 bg-white px-5 py-2.5 text-xs font-bold uppercase tracking-widest text-lucky-700 shadow-sm transition duration-150 ease-in-out hover:bg-lucky-50 hover:border-lucky-400 focus:outline-none focus:ring-2 focus:ring-lucky-400 focus:ring-offset-2 disabled:opacity-25 ${disabled && "opacity-25"} ` + className,
      disabled,
      children
    }
  );
}
function EmptyState({ icon = "📭", title, description, actionLabel, actionHref, className = "" }) {
  return /* @__PURE__ */ jsxs("div", { className: `flex flex-col items-center justify-center py-12 px-4 ${className}`, children: [
    /* @__PURE__ */ jsx("div", { className: "w-20 h-20 rounded-full bg-gradient-to-br from-lucky-50 to-lucky-100 flex items-center justify-center mb-4 animate-pulse-glow", children: /* @__PURE__ */ jsx("span", { className: "text-4xl", children: icon }) }),
    /* @__PURE__ */ jsx("h3", { className: "font-display text-lg text-gray-900 mb-1", children: title }),
    description && /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 text-center max-w-sm mb-4", children: description }),
    actionLabel && actionHref && /* @__PURE__ */ jsx(
      Link_default,
      {
        href: actionHref,
        className: "inline-flex items-center gap-2 px-5 py-2.5 lucky-gradient text-white font-bold text-sm rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all",
        children: actionLabel
      }
    )
  ] });
}
function PaymentBreakdown({ billAmount, discount, finalBill, platformFee, gst, gstRate, total, className = "" }) {
  return /* @__PURE__ */ jsxs("div", { className: `bg-gradient-to-br from-lucky-50 to-ticket-50 rounded-2xl border-2 border-dashed border-lucky-200 overflow-hidden ${className}`, children: [
    /* @__PURE__ */ jsxs("div", { className: "p-4 space-y-2", children: [
      /* @__PURE__ */ jsx(BreakdownRow, { label: "Total Bill", value: billAmount }),
      /* @__PURE__ */ jsx(BreakdownRow, { label: "Discount Applied", value: discount, prefix: "-", valueClass: "text-green-600" }),
      /* @__PURE__ */ jsx("div", { className: "border-t border-dashed border-lucky-200 pt-2", children: /* @__PURE__ */ jsx(BreakdownRow, { label: "Bill after Discount", value: finalBill, bold: true, valueClass: "text-lucky-700" }) }),
      /* @__PURE__ */ jsx(BreakdownRow, { label: "Platform Fee", value: platformFee, muted: true }),
      /* @__PURE__ */ jsx(BreakdownRow, { label: `GST (${gstRate}%)`, value: gst, muted: true })
    ] }),
    /* @__PURE__ */ jsx("div", { className: "bg-lucky-100/50 px-4 py-3 border-t-2 border-dashed border-lucky-300", children: /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center", children: [
      /* @__PURE__ */ jsxs("span", { className: "font-bold text-lucky-800 flex items-center gap-1.5", children: [
        /* @__PURE__ */ jsx("span", { className: "text-lg", children: "💰" }),
        " Total to Pay"
      ] }),
      /* @__PURE__ */ jsxs("span", { className: "text-xl font-bold text-lucky-800", children: [
        /* @__PURE__ */ jsx(CurrencySymbol, {}),
        total.toFixed(2)
      ] })
    ] }) })
  ] });
}
function BreakdownRow({ label, value, prefix = "", bold = false, muted = false, valueClass = "" }) {
  return /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center text-sm", children: [
    /* @__PURE__ */ jsx("span", { className: muted ? "text-gray-500" : "text-gray-700", children: label }),
    /* @__PURE__ */ jsxs("span", { className: `${bold ? "font-bold" : "font-semibold"} ${valueClass || (muted ? "text-gray-500" : "text-gray-900")}`, children: [
      prefix && /* @__PURE__ */ jsxs("span", { children: [
        prefix,
        " "
      ] }),
      /* @__PURE__ */ jsx(CurrencySymbol, {}),
      value.toFixed(2)
    ] })
  ] });
}
function ConfirmationModal({ show, onClose, title = "Payment Successful!", message, details = [], stampsEarned = 0 }) {
  const [animate, setAnimate] = useState(false);
  useEffect(() => {
    if (show) {
      const timer = setTimeout(() => setAnimate(true), 100);
      return () => clearTimeout(timer);
    }
    setAnimate(false);
  }, [show]);
  if (!show) return null;
  return /* @__PURE__ */ jsx("div", { className: "fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4", onClick: onClose, children: /* @__PURE__ */ jsxs(
    "div",
    {
      className: `bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center transform transition-all duration-500 ${animate ? "scale-100 opacity-100" : "scale-90 opacity-0"}`,
      onClick: (e2) => e2.stopPropagation(),
      children: [
        /* @__PURE__ */ jsx("div", { className: `mx-auto w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mb-5 transition-all duration-700 ${animate ? "scale-100" : "scale-0"}`, children: /* @__PURE__ */ jsx("svg", { className: `w-10 h-10 text-green-500 transition-all duration-500 delay-300 ${animate ? "opacity-100" : "opacity-0"}`, fill: "none", viewBox: "0 0 24 24", strokeWidth: "3", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", d: "M4.5 12.75l6 6 9-13.5" }) }) }),
        /* @__PURE__ */ jsx("h3", { className: "font-display text-xl text-gray-900 mb-1", children: title }),
        message && /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 mb-4", children: message }),
        details.length > 0 && /* @__PURE__ */ jsx("div", { className: "bg-gray-50 rounded-xl p-4 mb-4 text-left space-y-2", children: details.map((detail, i2) => /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-sm", children: [
          /* @__PURE__ */ jsx("span", { className: "text-gray-500", children: detail.label }),
          /* @__PURE__ */ jsx("span", { className: "font-bold text-gray-900", children: detail.value })
        ] }, i2)) }),
        stampsEarned > 0 && /* @__PURE__ */ jsx("div", { className: `bg-lucky-50 border-2 border-dashed border-lucky-200 rounded-xl p-4 mb-5 transition-all duration-700 delay-500 ${animate ? "opacity-100 translate-y-0" : "opacity-0 translate-y-4"}`, children: /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-center gap-2", children: [
          /* @__PURE__ */ jsx("span", { className: "text-2xl", children: "🎫" }),
          /* @__PURE__ */ jsxs("span", { className: "font-display text-lg text-lucky-700", children: [
            "+",
            stampsEarned,
            " Stamps Earned!"
          ] })
        ] }) }),
        /* @__PURE__ */ jsx(
          "button",
          {
            onClick: onClose,
            className: "w-full lucky-gradient text-white font-bold py-3 px-6 rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all text-sm",
            children: "Done"
          }
        )
      ]
    }
  ) });
}
function Index$1({ auth, coupons, locations, planName, stampsPerHundred, primaryCampaign, availableCampaigns, remainingRedeemAmount, maxRedeemableAmount }) {
  const { platform_fee, gst_rate, platform_fee_type, appDebug, flash } = usePage().props;
  const [confirmingRedemption, setConfirmingRedemption] = useState(false);
  const [selectedCoupon, setSelectedCoupon] = useState(null);
  const [modalStep, setModalStep] = useState(1);
  const [isProcessing, setIsProcessing] = useState(false);
  const [showSuccess, setShowSuccess] = useState(false);
  const [successData, setSuccessData] = useState(null);
  const { data, setData, processing, errors, reset } = useForm({
    merchant_location_id: "",
    amount: "",
    campaign_id: primaryCampaign?.id || ""
  });
  const selectedLocationName = locations.find((l2) => String(l2.id) === String(data.merchant_location_id))?.name;
  const confirmRedemption = (coupon) => {
    setSelectedCoupon(coupon);
    setConfirmingRedemption(true);
    setModalStep(1);
    if (coupon.merchant_location_id) {
      setData("merchant_location_id", coupon.merchant_location_id);
      setModalStep(2);
    } else {
      setData("merchant_location_id", "");
    }
  };
  const closeModal = () => {
    setConfirmingRedemption(false);
    setSelectedCoupon(null);
    setModalStep(1);
    setIsProcessing(false);
    reset();
  };
  const handlePayment = async (e2) => {
    e2.preventDefault();
    const couponId = selectedCoupon?.id;
    const formData = { ...data };
    if (!couponId) return;
    setIsProcessing(true);
    if (appDebug) {
      router3.post(route("coupons.redeem", couponId), formData, {
        onSuccess: () => {
          closeModal();
          setShowSuccess(true);
          setSuccessData({ stamps: breakdown.estimatedStamps });
        },
        onError: () => setIsProcessing(false)
      });
      return;
    }
    try {
      const response = await fetch(route("coupons.redeem", couponId), {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || ""
        },
        body: JSON.stringify(formData)
      });
      const result = await response.json();
      if (response.ok) {
        closeModal();
        const { order, transaction_id } = result;
        const options = {
          key: order.key,
          amount: order.amount,
          currency: order.currency,
          name: "Kutoot",
          description: `Payment for ${selectedCoupon.title}`,
          order_id: order.id,
          handler: function(response2) {
            router3.post(route("coupons.verify-payment", transaction_id), {
              razorpay_payment_id: response2.razorpay_payment_id,
              razorpay_order_id: response2.razorpay_order_id,
              razorpay_signature: response2.razorpay_signature
            }, {
              onSuccess: () => {
                setShowSuccess(true);
                setSuccessData({ stamps: breakdown.estimatedStamps });
              }
            });
          },
          prefill: {
            name: auth.user.name,
            email: auth.user.email
          },
          theme: { color: "#f08c10" },
          modal: {
            ondismiss: () => setIsProcessing(false)
          }
        };
        const rzp = new window.Razorpay(options);
        rzp.on("payment.failed", () => setIsProcessing(false));
        rzp.open();
      } else {
        alert(result.error || "Something went wrong");
        setIsProcessing(false);
      }
    } catch (error) {
      console.error("Payment initiation failed", error);
      alert("Payment initiation failed. Please try again.");
      setIsProcessing(false);
    }
  };
  const calculateBreakdown = () => {
    const billAmount = parseFloat(data.amount) || 0;
    let discount = 0;
    if (selectedCoupon) {
      if (selectedCoupon.discount_type === "percentage") {
        discount = billAmount * parseFloat(selectedCoupon.discount_value) / 100;
      } else {
        discount = parseFloat(selectedCoupon.discount_value) || 0;
      }
      if (selectedCoupon.max_discount_amount) {
        discount = Math.min(discount, parseFloat(selectedCoupon.max_discount_amount));
      }
    }
    discount = Math.min(discount, billAmount);
    const finalBill = Math.max(0, billAmount - discount);
    const fee = parseFloat(platform_fee);
    const feeAmount = platform_fee_type === "percentage" ? billAmount * fee / 100 : fee;
    const gst = feeAmount * gst_rate / 100;
    const total = finalBill + feeAmount + gst;
    const estimatedStamps = Math.floor(billAmount / 100) * stampsPerHundred;
    return { billAmount, discount, finalBill, feeAmount, gst, total, estimatedStamps };
  };
  const breakdown = calculateBreakdown();
  const balancePercent = maxRedeemableAmount > 0 ? Math.max(0, Math.min(100, remainingRedeemAmount / maxRedeemableAmount * 100)) : 0;
  return /* @__PURE__ */ jsxs(
    AuthenticatedLayout,
    {
      user: auth.user,
      header: /* @__PURE__ */ jsxs("h2", { className: "text-xl font-bold leading-tight text-white flex items-center gap-2", children: [
        "🎫 My Coupons (",
        planName,
        ")"
      ] }),
      children: [
        /* @__PURE__ */ jsx(Head_default, { title: "Coupons" }),
        /* @__PURE__ */ jsx("div", { className: "py-6 sm:py-8", children: /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-4 sm:px-6 lg:px-8", children: [
          auth.user && remainingRedeemAmount !== void 0 && /* @__PURE__ */ jsx("div", { className: "coupon-card p-4 sm:p-5 mb-6", children: /* @__PURE__ */ jsxs("div", { className: "flex flex-col sm:flex-row sm:items-center justify-between gap-3", children: [
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsxs("p", { className: "text-sm font-bold text-gray-900 flex items-center gap-1.5", children: [
                /* @__PURE__ */ jsx("span", { children: "💰" }),
                " Remaining Redeemable Balance"
              ] }),
              /* @__PURE__ */ jsxs("p", { className: "text-xs text-gray-500 mt-0.5", children: [
                /* @__PURE__ */ jsx(CurrencySymbol, {}),
                parseFloat(remainingRedeemAmount).toFixed(2),
                " of ",
                /* @__PURE__ */ jsx(CurrencySymbol, {}),
                parseFloat(maxRedeemableAmount).toFixed(2)
              ] })
            ] }),
            /* @__PURE__ */ jsx("div", { className: "w-full sm:w-48", children: /* @__PURE__ */ jsx("div", { className: "w-full bg-gray-200 rounded-full h-2.5 overflow-hidden", children: /* @__PURE__ */ jsx(
              "div",
              {
                className: `h-full rounded-full transition-all duration-1000 ${balancePercent > 50 ? "bg-green-500" : balancePercent > 20 ? "bg-amber-500" : "bg-red-500"}`,
                style: { width: `${balancePercent}%` }
              }
            ) }) })
          ] }) }),
          /* @__PURE__ */ jsx("div", { className: "grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6", children: coupons.data.length > 0 ? coupons.data.map((coupon) => /* @__PURE__ */ jsxs("div", { className: "coupon-card overflow-hidden flex flex-col hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group", children: [
            /* @__PURE__ */ jsxs("div", { className: "p-5 sm:p-6 flex-grow", children: [
              /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mb-3", children: [
                /* @__PURE__ */ jsx("span", { className: "inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold text-lucky-700 bg-lucky-100 rounded-full border border-lucky-200", children: coupon.merchant_location ? /* @__PURE__ */ jsxs(Fragment$1, { children: [
                  "📍 ",
                  coupon.merchant_location.branch_name
                ] }) : /* @__PURE__ */ jsx(Fragment$1, { children: "🌐 All Stores" }) }),
                /* @__PURE__ */ jsx("span", { className: "inline-flex items-center px-2.5 py-1 text-xs font-bold text-white bg-gradient-to-r from-green-500 to-emerald-500 rounded-full shadow-sm", children: coupon.discount_type === "percentage" ? `${coupon.discount_value}% OFF` : /* @__PURE__ */ jsxs(Fragment$1, { children: [
                  /* @__PURE__ */ jsx(CurrencySymbol, {}),
                  coupon.discount_value,
                  " OFF"
                ] }) })
              ] }),
              /* @__PURE__ */ jsx("h3", { className: "font-display text-lg text-gray-900 mb-1 group-hover:text-lucky-700 transition-colors", children: coupon.title }),
              coupon.description && /* @__PURE__ */ jsx("p", { className: "text-gray-500 text-sm mb-4 line-clamp-2", children: coupon.description }),
              /* @__PURE__ */ jsxs("div", { className: "bg-gradient-to-br from-lucky-50/50 to-ticket-50/50 p-3 rounded-xl text-sm border border-lucky-100 space-y-2", children: [
                /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center", children: [
                  /* @__PURE__ */ jsx("span", { className: "text-gray-500 text-xs", children: "Code" }),
                  /* @__PURE__ */ jsx("span", { className: "font-mono font-bold text-lucky-700 bg-lucky-100 px-2 py-0.5 rounded text-xs", children: coupon.code })
                ] }),
                (coupon.max_discount_amount || coupon.discount_type === "fixed") && /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center pt-1 border-t border-dashed border-lucky-100", children: [
                  /* @__PURE__ */ jsx("span", { className: "text-gray-500 text-xs", children: "Max Savings" }),
                  /* @__PURE__ */ jsxs("span", { className: "font-bold text-green-600 text-xs", children: [
                    /* @__PURE__ */ jsx(CurrencySymbol, {}),
                    parseFloat(coupon.max_discount_amount || coupon.discount_value).toFixed(2)
                  ] })
                ] }),
                coupon.min_order_value > 0 && /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center pt-1 border-t border-dashed border-lucky-100", children: [
                  /* @__PURE__ */ jsx("span", { className: "text-gray-500 text-xs", children: "Min Order" }),
                  /* @__PURE__ */ jsxs("span", { className: "font-bold text-gray-700 text-xs", children: [
                    /* @__PURE__ */ jsx(CurrencySymbol, {}),
                    parseFloat(coupon.min_order_value).toFixed(0)
                  ] })
                ] })
              ] })
            ] }),
            /* @__PURE__ */ jsx("div", { className: "flex justify-center gap-1.5 py-1.5 bg-gradient-to-r from-transparent via-lucky-50 to-transparent", children: [...Array(12)].map((_, i2) => /* @__PURE__ */ jsx("div", { className: "w-1.5 h-1.5 rounded-full bg-lucky-200" }, i2)) }),
            /* @__PURE__ */ jsx("div", { className: "bg-gradient-to-br from-lucky-50 to-ticket-50 px-5 py-3 sm:px-6 sm:py-4", children: auth.user ? /* @__PURE__ */ jsx(PrimaryButton, { className: "w-full justify-center", onClick: () => confirmRedemption(coupon), children: "🎟️ Redeem Now" }) : /* @__PURE__ */ jsx(
              Link_default,
              {
                href: route("login"),
                className: "inline-flex items-center w-full justify-center gap-2 px-5 py-2.5 lucky-gradient border border-transparent rounded-full font-bold text-xs text-white uppercase tracking-widest hover:shadow-lg transition-all",
                children: "🔑 Login to Redeem"
              }
            ) })
          ] }, coupon.id)) : /* @__PURE__ */ jsx("div", { className: "col-span-full", children: /* @__PURE__ */ jsx("div", { className: "coupon-card", children: /* @__PURE__ */ jsx(
            EmptyState,
            {
              icon: "🎭",
              title: "No coupons available",
              description: "Check back later or upgrade your plan to unlock more coupons and exclusive discounts.",
              actionLabel: "Upgrade Plan",
              actionHref: route("subscriptions.index")
            }
          ) }) }) })
        ] }) }),
        /* @__PURE__ */ jsx(Modal, { show: confirmingRedemption, onClose: closeModal, maxWidth: "lg", children: /* @__PURE__ */ jsxs("form", { onSubmit: handlePayment, className: "p-5 sm:p-6", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3 mb-1", children: [
            /* @__PURE__ */ jsx("div", { className: "w-10 h-10 rounded-xl bg-gradient-to-br from-lucky-100 to-lucky-200 flex items-center justify-center flex-shrink-0", children: /* @__PURE__ */ jsx("span", { className: "text-lg", children: "🎟️" }) }),
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx("h2", { className: "font-display text-lg text-gray-900", children: selectedCoupon?.title }),
              selectedLocationName && /* @__PURE__ */ jsxs("p", { className: "text-xs text-lucky-600", children: [
                "at ",
                selectedLocationName
              ] })
            ] })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 my-5", children: [
            /* @__PURE__ */ jsx(StepDot, { step: 1, current: modalStep, label: "Location" }),
            /* @__PURE__ */ jsx("div", { className: "flex-1 h-0.5 bg-gray-200 rounded", children: /* @__PURE__ */ jsx("div", { className: `h-full rounded transition-all duration-300 ${modalStep >= 2 ? "bg-lucky-400 w-full" : "w-0"}` }) }),
            /* @__PURE__ */ jsx(StepDot, { step: 2, current: modalStep, label: "Bill" }),
            /* @__PURE__ */ jsx("div", { className: "flex-1 h-0.5 bg-gray-200 rounded", children: /* @__PURE__ */ jsx("div", { className: `h-full rounded transition-all duration-300 ${modalStep >= 3 ? "bg-lucky-400 w-full" : "w-0"}` }) }),
            /* @__PURE__ */ jsx(StepDot, { step: 3, current: modalStep, label: "Pay" })
          ] }),
          modalStep === 1 && /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
            /* @__PURE__ */ jsx(InputLabel, { htmlFor: "merchant_location_id", value: "Select Store Location" }),
            /* @__PURE__ */ jsxs(
              "select",
              {
                id: "merchant_location_id",
                value: data.merchant_location_id,
                onChange: (e2) => setData("merchant_location_id", e2.target.value),
                className: "block w-full border-lucky-200 focus:border-lucky-500 focus:ring-lucky-500 rounded-xl shadow-sm text-sm",
                required: true,
                children: [
                  /* @__PURE__ */ jsx("option", { value: "", children: "Choose a location..." }),
                  locations.map((loc) => /* @__PURE__ */ jsx("option", { value: loc.id, children: loc.name }, loc.id))
                ]
              }
            ),
            /* @__PURE__ */ jsx(InputError, { message: errors.merchant_location_id, className: "mt-1" }),
            /* @__PURE__ */ jsx("div", { className: "flex justify-end", children: /* @__PURE__ */ jsx(
              PrimaryButton,
              {
                type: "button",
                disabled: !data.merchant_location_id,
                onClick: () => setModalStep(2),
                children: "Next →"
              }
            ) })
          ] }),
          modalStep === 2 && /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
            /* @__PURE__ */ jsx(InputLabel, { htmlFor: "amount", value: /* @__PURE__ */ jsxs("span", { children: [
              "Enter Bill Amount (",
              /* @__PURE__ */ jsx(CurrencySymbol, {}),
              ")"
            ] }) }),
            /* @__PURE__ */ jsx(
              TextInput,
              {
                id: "amount",
                type: "number",
                step: "0.01",
                min: "1",
                value: data.amount,
                onChange: (e2) => setData("amount", e2.target.value),
                className: "block w-full text-lg",
                placeholder: "e.g. 500.00",
                autoFocus: true,
                required: true
              }
            ),
            /* @__PURE__ */ jsx(InputError, { message: errors.amount, className: "mt-1" }),
            /* @__PURE__ */ jsxs("div", { className: "flex justify-between", children: [
              /* @__PURE__ */ jsx(SecondaryButton, { type: "button", onClick: () => setModalStep(1), children: "← Back" }),
              /* @__PURE__ */ jsx(
                PrimaryButton,
                {
                  type: "button",
                  disabled: !data.amount || parseFloat(data.amount) <= 0,
                  onClick: () => setModalStep(3),
                  children: "Review →"
                }
              )
            ] })
          ] }),
          modalStep === 3 && /* @__PURE__ */ jsxs("div", { className: "space-y-4", children: [
            /* @__PURE__ */ jsxs("div", { className: "bg-gray-50 rounded-xl p-3 text-sm space-y-1", children: [
              /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-500", children: [
                /* @__PURE__ */ jsx("span", { children: "📍 Location" }),
                /* @__PURE__ */ jsx("span", { className: "font-medium text-gray-900", children: selectedLocationName })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-gray-500", children: [
                /* @__PURE__ */ jsx("span", { children: "🎫 Coupon" }),
                /* @__PURE__ */ jsx("span", { className: "font-medium text-gray-900", children: selectedCoupon?.code })
              ] })
            ] }),
            /* @__PURE__ */ jsx(
              PaymentBreakdown,
              {
                billAmount: breakdown.billAmount,
                discount: breakdown.discount,
                finalBill: breakdown.finalBill,
                platformFee: breakdown.feeAmount,
                gst: breakdown.gst,
                gstRate: gst_rate,
                total: breakdown.total
              }
            ),
            breakdown.estimatedStamps > 0 && /* @__PURE__ */ jsxs("div", { className: "bg-green-50 p-3 rounded-xl border border-green-200 flex items-center gap-3", children: [
              /* @__PURE__ */ jsx("div", { className: "w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0", children: /* @__PURE__ */ jsx("span", { className: "text-green-700 font-bold text-lg", children: breakdown.estimatedStamps }) }),
              /* @__PURE__ */ jsxs("div", { children: [
                /* @__PURE__ */ jsxs("p", { className: "text-sm font-semibold text-green-800", children: [
                  "You'll earn ",
                  breakdown.estimatedStamps,
                  " stamp",
                  breakdown.estimatedStamps !== 1 ? "s" : ""
                ] }),
                /* @__PURE__ */ jsxs("p", { className: "text-xs text-green-600", children: [
                  stampsPerHundred,
                  " stamp",
                  stampsPerHundred !== 1 ? "s" : "",
                  " per ",
                  /* @__PURE__ */ jsx(CurrencySymbol, {}),
                  "100"
                ] })
              ] })
            ] }),
            primaryCampaign ? /* @__PURE__ */ jsx("div", { className: "bg-amber-50 p-3 rounded-xl border border-amber-200", children: /* @__PURE__ */ jsxs("p", { className: "text-sm text-amber-800", children: [
              /* @__PURE__ */ jsx("span", { className: "font-semibold", children: "Stamps go to:" }),
              " ",
              primaryCampaign.reward_name
            ] }) }) : availableCampaigns?.length > 0 ? /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx(InputLabel, { htmlFor: "campaign_id", value: "Select Campaign for Stamps" }),
              /* @__PURE__ */ jsxs(
                "select",
                {
                  id: "campaign_id",
                  value: data.campaign_id,
                  onChange: (e2) => setData("campaign_id", e2.target.value),
                  className: "mt-1 block w-full border-lucky-200 focus:border-lucky-500 focus:ring-lucky-500 rounded-xl shadow-sm text-sm",
                  required: true,
                  children: [
                    /* @__PURE__ */ jsx("option", { value: "", children: "Choose a campaign" }),
                    availableCampaigns.map((c2) => /* @__PURE__ */ jsx("option", { value: c2.id, children: c2.reward_name }, c2.id))
                  ]
                }
              ),
              /* @__PURE__ */ jsx(InputError, { message: errors.campaign_id, className: "mt-1" })
            ] }) : null,
            /* @__PURE__ */ jsxs("div", { className: "flex justify-between pt-2", children: [
              /* @__PURE__ */ jsx(SecondaryButton, { type: "button", onClick: () => setModalStep(2), children: "← Back" }),
              /* @__PURE__ */ jsx(PrimaryButton, { disabled: isProcessing || processing, className: "min-w-[140px] justify-center", children: isProcessing ? /* @__PURE__ */ jsxs("span", { className: "flex items-center gap-2", children: [
                /* @__PURE__ */ jsxs("svg", { className: "animate-spin h-4 w-4", viewBox: "0 0 24 24", children: [
                  /* @__PURE__ */ jsx("circle", { className: "opacity-25", cx: "12", cy: "12", r: "10", stroke: "currentColor", strokeWidth: "4", fill: "none" }),
                  /* @__PURE__ */ jsx("path", { className: "opacity-75", fill: "currentColor", d: "M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" })
                ] }),
                "Processing..."
              ] }) : appDebug ? "🐛 Debug Redeem" : /* @__PURE__ */ jsxs(Fragment$1, { children: [
                "Pay ",
                /* @__PURE__ */ jsx(CurrencySymbol, {}),
                breakdown.total.toFixed(2)
              ] }) })
            ] })
          ] })
        ] }) }),
        /* @__PURE__ */ jsx(
          ConfirmationModal,
          {
            show: showSuccess,
            onClose: () => {
              setShowSuccess(false);
              setSuccessData(null);
            },
            title: "Coupon Redeemed!",
            message: "Your coupon has been successfully redeemed.",
            stampsEarned: successData?.stamps || 0
          }
        )
      ]
    }
  );
}
function StepDot({ step, current, label }) {
  const isActive = current >= step;
  return /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-center gap-1", children: [
    /* @__PURE__ */ jsx("div", { className: `w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 ${isActive ? "lucky-gradient text-white shadow-md" : "bg-gray-200 text-gray-500"}`, children: current > step ? /* @__PURE__ */ jsx("svg", { className: "w-3.5 h-3.5", fill: "currentColor", viewBox: "0 0 20 20", children: /* @__PURE__ */ jsx("path", { fillRule: "evenodd", d: "M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z", clipRule: "evenodd" }) }) : step }),
    /* @__PURE__ */ jsx("span", { className: `text-xs font-medium ${isActive ? "text-lucky-600" : "text-gray-400"}`, children: label })
  ] });
}
const __vite_glob_0_9 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Index$1
}, Symbol.toStringTag, { value: "Module" }));
function Dashboard({ auth, user, plan, primaryCampaign, stats, recentActivity, stamps: initialStamps, activityLogs }) {
  const allStatsZero = stats.stamps_count === 0 && stats.total_coupons_used === 0 && stats.total_discount_redeemed === 0;
  const [stamps, setStamps] = useState(initialStamps);
  const [editingStamp, setEditingStamp] = useState(null);
  const [slotValues, setSlotValues] = useState([]);
  const [editError, setEditError] = useState("");
  const [editSubmitting, setEditSubmitting] = useState(false);
  const openEditModal = useCallback((stamp) => {
    const config2 = stamp.stamp_config;
    if (!config2) return;
    setEditingStamp(stamp);
    setSlotValues(Array(config2.slots).fill(config2.min));
    setEditError("");
  }, []);
  const closeEditModal = useCallback(() => {
    setEditingStamp(null);
    setSlotValues([]);
    setEditError("");
  }, []);
  const handleSlotChange = useCallback((index, value) => {
    setSlotValues((prev) => {
      const next = [...prev];
      next[index] = parseInt(value) || 0;
      return next;
    });
  }, []);
  const submitStampEdit = useCallback(async () => {
    if (!editingStamp) return;
    setEditSubmitting(true);
    setEditError("");
    try {
      const response = await axios.patch(`/api/stamps/${editingStamp.id}/code`, {
        slot_values: slotValues
      });
      setStamps((prev) => prev.map(
        (s2) => s2.id === editingStamp.id ? { ...s2, code: response.data.stamp.code, is_editable: true } : s2
      ));
      closeEditModal();
    } catch (err) {
      setEditError(err.response?.data?.message || "Failed to update stamp code.");
    } finally {
      setEditSubmitting(false);
    }
  }, [editingStamp, slotValues, closeEditModal]);
  const previewCode = editingStamp?.stamp_config ? (() => {
    const config2 = editingStamp.stamp_config;
    const digits = String(config2.max).length;
    const code = editingStamp.code.split("-")[0] || "CODE";
    const paddedSlots = slotValues.map((v2) => String(v2).padStart(digits, "0"));
    return code + "-" + paddedSlots.join("-");
  })() : "";
  return /* @__PURE__ */ jsxs(
    AuthenticatedLayout,
    {
      user: auth.user,
      header: /* @__PURE__ */ jsx("h2", { className: "text-xl font-bold leading-tight text-white flex items-center gap-2", children: "🎯 Dashboard" }),
      children: [
        /* @__PURE__ */ jsx(Head_default, { title: "Dashboard" }),
        /* @__PURE__ */ jsx("div", { className: "py-6 sm:py-8", children: /* @__PURE__ */ jsxs("div", { className: "mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6", children: [
          /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 lg:grid-cols-2 gap-6", children: [
            /* @__PURE__ */ jsxs("div", { className: "coupon-card p-5 sm:p-6", children: [
              /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-4 mb-5", children: [
                /* @__PURE__ */ jsx("div", { className: "w-14 h-14 rounded-2xl bg-gradient-to-br from-lucky-400 to-ticket-400 flex items-center justify-center text-white text-2xl font-bold shadow-lg flex-shrink-0", children: user.name.charAt(0).toUpperCase() }),
                /* @__PURE__ */ jsxs("div", { className: "min-w-0", children: [
                  /* @__PURE__ */ jsxs("h3", { className: "font-display text-lg text-gray-900 truncate", children: [
                    "Welcome back, ",
                    user.name.split(" ")[0],
                    "!"
                  ] }),
                  /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 truncate", children: user.email })
                ] })
              ] }),
              /* @__PURE__ */ jsxs("dl", { className: "space-y-0", children: [
                /* @__PURE__ */ jsx(ProfileRow, { icon: "👤", label: "Full Name", value: user.name }),
                /* @__PURE__ */ jsx(ProfileRow, { icon: "📧", label: "Email", value: user.email }),
                /* @__PURE__ */ jsx(ProfileRow, { icon: "📅", label: "Member Since", value: user.created_at }),
                primaryCampaign && /* @__PURE__ */ jsx(ProfileRow, { icon: "🏆", label: "Campaign", value: primaryCampaign, valueClass: "text-lucky-600", last: true })
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "coupon-card overflow-visible", children: [
              plan && !plan.is_default && /* @__PURE__ */ jsx("div", { className: "absolute -top-3 left-6 z-10", children: /* @__PURE__ */ jsx("span", { className: "golden-badge px-4 py-1 rounded-full text-xs", children: "⭐ ACTIVE PLAN" }) }),
              /* @__PURE__ */ jsxs("div", { className: "p-5 sm:p-6", children: [
                /* @__PURE__ */ jsxs("h3", { className: "font-display text-lg text-gray-900 mb-4 flex items-center gap-2", children: [
                  /* @__PURE__ */ jsx("span", { className: "text-2xl", children: "🎫" }),
                  " Plan Details"
                ] }),
                plan ? /* @__PURE__ */ jsxs(Fragment$1, { children: [
                  /* @__PURE__ */ jsxs("p", { className: "text-2xl font-display text-lucky-600 mb-4", children: [
                    plan.name,
                    plan.is_default && /* @__PURE__ */ jsx("span", { className: "ml-2 text-xs font-normal text-gray-400 font-sans", children: "(Free)" })
                  ] }),
                  /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-3 gap-2 sm:gap-3 text-sm", children: [
                    /* @__PURE__ */ jsx(PlanMetric, { value: plan.stamps_on_purchase, label: "Bonus Stamps", color: "lucky" }),
                    /* @__PURE__ */ jsx(PlanMetric, { value: plan.stamps_per_100, label: /* @__PURE__ */ jsxs(Fragment$1, { children: [
                      "Per ",
                      /* @__PURE__ */ jsx(CurrencySymbol, {}),
                      "100"
                    ] }), color: "lucky" }),
                    /* @__PURE__ */ jsx(PlanMetric, { value: plan.max_discounted_bills, label: "Max Bills", color: "ticket" })
                  ] }),
                  /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 gap-2 sm:gap-3 text-sm mt-2 sm:mt-3", children: [
                    /* @__PURE__ */ jsx(PlanMetric, { value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
                      /* @__PURE__ */ jsx(CurrencySymbol, {}),
                      plan.max_redeemable_amount.toFixed(0)
                    ] }), label: "Max Redeem", color: "ticket" }),
                    plan.duration_days && /* @__PURE__ */ jsx(PlanMetric, { value: plan.duration_days, label: "Days Validity", color: "prize" })
                  ] }),
                  plan.days_remaining !== null && plan.days_remaining >= 0 && plan.duration_days && /* @__PURE__ */ jsxs("div", { className: "mt-4 bg-gray-50 rounded-xl p-3", children: [
                    /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-xs text-gray-500 mb-1.5", children: [
                      /* @__PURE__ */ jsx("span", { children: "Time Remaining" }),
                      /* @__PURE__ */ jsxs("span", { className: `font-bold ${plan.days_remaining <= 7 ? "text-red-600" : "text-green-600"}`, children: [
                        plan.days_remaining,
                        " days left"
                      ] })
                    ] }),
                    /* @__PURE__ */ jsx("div", { className: "w-full bg-gray-200 rounded-full h-2 overflow-hidden", children: /* @__PURE__ */ jsx(
                      "div",
                      {
                        className: `h-full rounded-full transition-all duration-1000 ${plan.days_remaining <= 7 ? "bg-red-500" : plan.days_remaining <= 14 ? "bg-amber-500" : "bg-green-500"}`,
                        style: { width: `${Math.min(100, plan.days_remaining / plan.duration_days * 100)}%` }
                      }
                    ) }),
                    /* @__PURE__ */ jsxs("div", { className: "flex justify-between text-xs text-gray-400 mt-1", children: [
                      plan.purchased_at && /* @__PURE__ */ jsx("span", { children: plan.purchased_at }),
                      plan.expires_at && /* @__PURE__ */ jsx("span", { children: plan.expires_at })
                    ] })
                  ] })
                ] }) : /* @__PURE__ */ jsx(
                  EmptyState,
                  {
                    icon: "🎭",
                    title: "No active plan",
                    description: "Upgrade to unlock more coupons and earn more stamps!",
                    actionLabel: "Browse Plans",
                    actionHref: route("subscriptions.index")
                  }
                )
              ] })
            ] })
          ] }),
          allStatsZero ? /* @__PURE__ */ jsx("div", { className: "coupon-card p-6", children: /* @__PURE__ */ jsx(
            EmptyState,
            {
              icon: "🚀",
              title: "Your journey starts here!",
              description: "Redeem a coupon at a partner store to earn your first stamps and see your stats come alive.",
              actionLabel: "Browse Coupons",
              actionHref: route("coupons.index")
            }
          ) }) : /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4", children: [
            /* @__PURE__ */ jsx(StatCard, { label: "Total Stamps", value: stats.stamps_count, icon: "🎫", color: "lucky" }),
            /* @__PURE__ */ jsx(StatCard, { label: "Coupons Used", value: stats.total_coupons_used, icon: "🎟️", color: "green" }),
            /* @__PURE__ */ jsx(StatCard, { label: "Discount Saved", value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
              /* @__PURE__ */ jsx(CurrencySymbol, {}),
              stats.total_discount_redeemed.toFixed(0)
            ] }), icon: "💰", color: "emerald" }),
            /* @__PURE__ */ jsx(StatCard, { label: "Bills Left", value: stats.remaining_bills, icon: "📋", color: "amber" }),
            /* @__PURE__ */ jsx(StatCard, { label: "Redeem Left", value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
              /* @__PURE__ */ jsx(CurrencySymbol, {}),
              stats.remaining_redeem_amount.toFixed(0)
            ] }), icon: "🎁", color: "rose" })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "coupon-card overflow-hidden", children: [
            /* @__PURE__ */ jsx("div", { className: "p-5 sm:p-6 pb-0 sm:pb-0", children: /* @__PURE__ */ jsxs("h3", { className: "font-display text-lg text-gray-900 mb-4 flex items-center gap-2", children: [
              /* @__PURE__ */ jsx("span", { className: "text-xl", children: "📜" }),
              " Recent Activity"
            ] }) }),
            recentActivity.length > 0 ? /* @__PURE__ */ jsxs(Fragment$1, { children: [
              /* @__PURE__ */ jsx("div", { className: "hidden md:block overflow-x-auto px-5 sm:px-6 pb-5 sm:pb-6", children: /* @__PURE__ */ jsxs("table", { className: "min-w-full text-sm", children: [
                /* @__PURE__ */ jsx("thead", { children: /* @__PURE__ */ jsxs("tr", { className: "border-b-2 border-dashed border-lucky-200 text-left text-lucky-600", children: [
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold", children: "Coupon" }),
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold", children: "Location" }),
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold text-right", children: "Bill" }),
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold text-right", children: "Discount" }),
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold text-right", children: "Paid" }),
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold text-center", children: "Stamps" }),
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold text-right", children: "When" })
                ] }) }),
                /* @__PURE__ */ jsx("tbody", { className: "divide-y divide-dashed divide-lucky-100", children: recentActivity.map((a2) => /* @__PURE__ */ jsxs("tr", { className: "hover:bg-lucky-50/50 transition-colors", children: [
                  /* @__PURE__ */ jsx("td", { className: "py-2.5 text-gray-900 font-medium", children: a2.coupon_title ?? "—" }),
                  /* @__PURE__ */ jsx("td", { className: "py-2.5 text-gray-600 text-xs", children: a2.location_name ?? "—" }),
                  /* @__PURE__ */ jsxs("td", { className: "py-2.5 text-right text-gray-700", children: [
                    /* @__PURE__ */ jsx(CurrencySymbol, {}),
                    a2.original_bill_amount.toFixed(2)
                  ] }),
                  /* @__PURE__ */ jsx("td", { className: "py-2.5 text-right font-bold text-green-600", children: a2.discount_amount > 0 ? /* @__PURE__ */ jsxs(Fragment$1, { children: [
                    "-",
                    /* @__PURE__ */ jsx(CurrencySymbol, {}),
                    a2.discount_amount.toFixed(2)
                  ] }) : "—" }),
                  /* @__PURE__ */ jsxs("td", { className: "py-2.5 text-right font-bold text-lucky-700", children: [
                    /* @__PURE__ */ jsx(CurrencySymbol, {}),
                    a2.total_paid.toFixed(2)
                  ] }),
                  /* @__PURE__ */ jsx("td", { className: "py-2.5 text-center", children: a2.stamps_earned > 0 ? /* @__PURE__ */ jsxs("span", { className: "inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-lucky-100 text-lucky-700 text-xs font-bold", children: [
                    "🎫 ",
                    a2.stamps_earned
                  ] }) : "—" }),
                  /* @__PURE__ */ jsx("td", { className: "py-2.5 text-right text-gray-400 text-xs", children: a2.created_at })
                ] }, a2.id)) })
              ] }) }),
              /* @__PURE__ */ jsx("div", { className: "md:hidden space-y-3 px-5 pb-5", children: recentActivity.map((a2) => /* @__PURE__ */ jsxs("div", { className: "bg-lucky-50/30 rounded-xl p-4 border border-lucky-100", children: [
                /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start mb-2", children: [
                  /* @__PURE__ */ jsxs("div", { children: [
                    /* @__PURE__ */ jsx("p", { className: "font-medium text-gray-900 text-sm", children: a2.coupon_title ?? "Transaction" }),
                    /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-500", children: a2.location_name ?? "" })
                  ] }),
                  a2.stamps_earned > 0 && /* @__PURE__ */ jsxs("span", { className: "inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-lucky-100 text-lucky-700 text-xs font-bold", children: [
                    "🎫 ",
                    a2.stamps_earned
                  ] })
                ] }),
                /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center text-sm", children: [
                  /* @__PURE__ */ jsxs("div", { children: [
                    /* @__PURE__ */ jsx("span", { className: "text-gray-500", children: "Bill: " }),
                    /* @__PURE__ */ jsxs("span", { className: "text-gray-700", children: [
                      /* @__PURE__ */ jsx(CurrencySymbol, {}),
                      a2.original_bill_amount.toFixed(2)
                    ] }),
                    a2.discount_amount > 0 && /* @__PURE__ */ jsxs("span", { className: "ml-2 text-green-600 font-bold text-xs", children: [
                      "-",
                      /* @__PURE__ */ jsx(CurrencySymbol, {}),
                      a2.discount_amount.toFixed(2)
                    ] })
                  ] }),
                  /* @__PURE__ */ jsxs("span", { className: "font-bold text-lucky-700", children: [
                    /* @__PURE__ */ jsx(CurrencySymbol, {}),
                    a2.total_paid.toFixed(2)
                  ] })
                ] }),
                /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-400 mt-1", children: a2.created_at })
              ] }, a2.id)) })
            ] }) : /* @__PURE__ */ jsx("div", { className: "px-5 sm:px-6 pb-5 sm:pb-6", children: /* @__PURE__ */ jsx(
              EmptyState,
              {
                icon: "💤",
                title: "No activity yet",
                description: "Your recent transactions will appear here once you redeem a coupon.",
                actionLabel: "Browse Coupons",
                actionHref: route("coupons.index")
              }
            ) })
          ] }),
          /* @__PURE__ */ jsxs("div", { className: "coupon-card overflow-hidden", children: [
            /* @__PURE__ */ jsx("div", { className: "p-5 sm:p-6 pb-0 sm:pb-0", children: /* @__PURE__ */ jsxs("h3", { className: "font-display text-lg text-gray-900 mb-4 flex items-center gap-2", children: [
              /* @__PURE__ */ jsx("span", { className: "text-xl", children: "🎫" }),
              " My Stamps"
            ] }) }),
            stamps.length > 0 ? /* @__PURE__ */ jsxs(Fragment$1, { children: [
              /* @__PURE__ */ jsx("div", { className: "hidden md:block overflow-x-auto px-5 sm:px-6 pb-5 sm:pb-6", children: /* @__PURE__ */ jsxs("table", { className: "min-w-full text-sm", children: [
                /* @__PURE__ */ jsx("thead", { children: /* @__PURE__ */ jsxs("tr", { className: "border-b-2 border-dashed border-lucky-200 text-left text-lucky-600", children: [
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold", children: "Code" }),
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold", children: "Source" }),
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold", children: "Campaign" }),
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold text-right", children: "Bill Amount" }),
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold text-right", children: "Earned" }),
                  /* @__PURE__ */ jsx("th", { className: "pb-2 font-bold text-center", children: "Action" })
                ] }) }),
                /* @__PURE__ */ jsx("tbody", { className: "divide-y divide-dashed divide-lucky-100", children: stamps.map((s2) => /* @__PURE__ */ jsxs("tr", { className: "hover:bg-lucky-50/50 transition-colors", children: [
                  /* @__PURE__ */ jsx("td", { className: "py-2.5", children: /* @__PURE__ */ jsx("span", { className: "font-mono text-xs bg-lucky-100 text-lucky-700 px-2 py-0.5 rounded", children: s2.code }) }),
                  /* @__PURE__ */ jsx("td", { className: "py-2.5", children: /* @__PURE__ */ jsxs("span", { className: `inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium ${s2.source === "Plan Purchase" ? "bg-ticket-100 text-ticket-700" : s2.source === "Coupon Redemption" ? "bg-green-100 text-green-700" : "bg-lucky-100 text-lucky-700"}`, children: [
                    s2.source === "Plan Purchase" ? "⭐" : s2.source === "Coupon Redemption" ? "🎟️" : "🧾",
                    " ",
                    s2.source
                  ] }) }),
                  /* @__PURE__ */ jsx("td", { className: "py-2.5 text-gray-700 font-medium", children: s2.campaign_name ?? "—" }),
                  /* @__PURE__ */ jsx("td", { className: "py-2.5 text-right text-gray-600", children: s2.bill_amount > 0 ? /* @__PURE__ */ jsxs(Fragment$1, { children: [
                    /* @__PURE__ */ jsx(CurrencySymbol, {}),
                    s2.bill_amount.toFixed(2)
                  ] }) : "—" }),
                  /* @__PURE__ */ jsx("td", { className: "py-2.5 text-right text-gray-400 text-xs", children: s2.created_at }),
                  /* @__PURE__ */ jsxs("td", { className: "py-2.5 text-center", children: [
                    s2.is_editable && s2.stamp_config && /* @__PURE__ */ jsx(
                      "button",
                      {
                        onClick: () => openEditModal(s2),
                        className: "inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-ticket-100 text-ticket-700 hover:bg-ticket-200 transition-colors",
                        children: "✏️ Pick Numbers"
                      }
                    ),
                    s2.is_editable && s2.editable_until && /* @__PURE__ */ jsx(StampCountdown, { editableUntil: s2.editable_until })
                  ] })
                ] }, s2.id)) })
              ] }) }),
              /* @__PURE__ */ jsx("div", { className: "md:hidden space-y-3 px-5 pb-5", children: stamps.map((s2) => /* @__PURE__ */ jsxs("div", { className: "bg-lucky-50/30 rounded-xl p-3 border border-lucky-100", children: [
                /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3", children: [
                  /* @__PURE__ */ jsx("div", { className: `w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 ${s2.source === "Plan Purchase" ? "bg-ticket-100" : s2.source === "Coupon Redemption" ? "bg-green-100" : "bg-lucky-100"}`, children: /* @__PURE__ */ jsx("span", { className: "text-sm", children: s2.source === "Plan Purchase" ? "⭐" : s2.source === "Coupon Redemption" ? "🎟️" : "🧾" }) }),
                  /* @__PURE__ */ jsxs("div", { className: "flex-1 min-w-0", children: [
                    /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-baseline", children: [
                      /* @__PURE__ */ jsx("span", { className: "font-mono text-xs bg-lucky-100 text-lucky-700 px-1.5 py-0.5 rounded", children: s2.code }),
                      /* @__PURE__ */ jsx("span", { className: "text-xs text-gray-400", children: s2.created_at })
                    ] }),
                    /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-500 mt-0.5 truncate", children: s2.campaign_name ?? "No campaign" })
                  ] })
                ] }),
                s2.is_editable && s2.stamp_config && /* @__PURE__ */ jsxs("div", { className: "mt-2 flex items-center justify-between", children: [
                  /* @__PURE__ */ jsx(
                    "button",
                    {
                      onClick: () => openEditModal(s2),
                      className: "inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-ticket-100 text-ticket-700 hover:bg-ticket-200 transition-colors",
                      children: "✏️ Pick Numbers"
                    }
                  ),
                  s2.editable_until && /* @__PURE__ */ jsx(StampCountdown, { editableUntil: s2.editable_until })
                ] })
              ] }, s2.id)) })
            ] }) : /* @__PURE__ */ jsx("div", { className: "px-5 sm:px-6 pb-5 sm:pb-6", children: /* @__PURE__ */ jsx(EmptyState, { icon: "🎭", title: "No stamps collected yet", description: "Earn stamps by purchasing a plan or redeeming coupons at partner stores." }) })
          ] }),
          editingStamp && editingStamp.stamp_config && /* @__PURE__ */ jsx("div", { className: "fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4", onClick: closeEditModal, children: /* @__PURE__ */ jsxs("div", { className: "bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative", onClick: (e2) => e2.stopPropagation(), children: [
            /* @__PURE__ */ jsx("button", { onClick: closeEditModal, className: "absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-xl", children: "×" }),
            /* @__PURE__ */ jsx("h3", { className: "font-display text-lg text-gray-900 mb-1 flex items-center gap-2", children: "🎯 Pick Your Numbers" }),
            /* @__PURE__ */ jsxs("p", { className: "text-xs text-gray-500 mb-4", children: [
              "Choose ",
              editingStamp.stamp_config.slots,
              " numbers between ",
              editingStamp.stamp_config.min,
              " and ",
              editingStamp.stamp_config.max,
              " in ascending order."
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "bg-lucky-50 rounded-xl p-3 mb-4 text-center border border-lucky-200", children: [
              /* @__PURE__ */ jsx("p", { className: "text-xs text-lucky-600 font-medium mb-1", children: "Preview" }),
              /* @__PURE__ */ jsx("p", { className: "font-mono text-lg font-bold text-lucky-700", children: previewCode })
            ] }),
            /* @__PURE__ */ jsx("div", { className: "grid grid-cols-3 gap-2 mb-4", children: slotValues.map((val, idx) => /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsxs("label", { className: "text-xs text-gray-500 font-medium", children: [
                "Slot ",
                idx + 1
              ] }),
              /* @__PURE__ */ jsx(
                "input",
                {
                  type: "number",
                  min: editingStamp.stamp_config.min,
                  max: editingStamp.stamp_config.max,
                  value: val,
                  onChange: (e2) => handleSlotChange(idx, e2.target.value),
                  className: "w-full rounded-lg border-gray-300 text-center font-mono text-sm focus:border-lucky-500 focus:ring-lucky-500"
                }
              )
            ] }, idx)) }),
            editError && /* @__PURE__ */ jsx("div", { className: "bg-red-50 text-red-700 text-xs rounded-lg p-2.5 mb-3 border border-red-200", children: editError }),
            editingStamp.editable_until && /* @__PURE__ */ jsx("div", { className: "text-center mb-3", children: /* @__PURE__ */ jsx(StampCountdown, { editableUntil: editingStamp.editable_until, showLabel: true }) }),
            /* @__PURE__ */ jsxs("div", { className: "flex gap-2", children: [
              /* @__PURE__ */ jsx(
                "button",
                {
                  onClick: closeEditModal,
                  className: "flex-1 px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition-colors",
                  children: "Cancel"
                }
              ),
              /* @__PURE__ */ jsx(
                "button",
                {
                  onClick: submitStampEdit,
                  disabled: editSubmitting,
                  className: "flex-1 px-4 py-2.5 rounded-xl lucky-gradient text-white text-sm font-bold shadow-md hover:shadow-lg transition-all disabled:opacity-50",
                  children: editSubmitting ? "Saving..." : "Confirm Numbers"
                }
              )
            ] })
          ] }) }),
          activityLogs.length > 0 && /* @__PURE__ */ jsxs("div", { className: "coupon-card p-5 sm:p-6", children: [
            /* @__PURE__ */ jsxs("h3", { className: "font-display text-lg text-gray-900 mb-4 flex items-center gap-2", children: [
              /* @__PURE__ */ jsx("span", { className: "text-xl", children: "📋" }),
              " Activity Log"
            ] }),
            /* @__PURE__ */ jsx("ul", { className: "space-y-2", children: activityLogs.map((log) => /* @__PURE__ */ jsxs("li", { className: "flex items-start gap-3 text-sm p-2.5 rounded-xl hover:bg-lucky-50/50 transition-colors", children: [
              /* @__PURE__ */ jsx("span", { className: "flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-lucky-100 to-lucky-200 flex items-center justify-center text-lucky-600 text-xs font-bold", children: log.icon ?? "⚡" }),
              /* @__PURE__ */ jsxs("div", { className: "flex-1 min-w-0", children: [
                /* @__PURE__ */ jsx("p", { className: "text-gray-900 text-sm", children: log.description }),
                /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-400 mt-0.5", children: log.created_at })
              ] })
            ] }, log.id)) })
          ] })
        ] }) })
      ]
    }
  );
}
function ProfileRow({ icon, label, value, valueClass = "text-gray-900", last = false }) {
  return /* @__PURE__ */ jsxs("div", { className: `flex justify-between py-2.5 text-sm ${!last ? "border-b border-dashed border-lucky-100" : ""}`, children: [
    /* @__PURE__ */ jsxs("dt", { className: "text-gray-500 flex items-center gap-1.5", children: [
      /* @__PURE__ */ jsx("span", { children: icon }),
      " ",
      label
    ] }),
    /* @__PURE__ */ jsx("dd", { className: `font-bold ${valueClass} truncate ml-4`, children: value })
  ] });
}
function PlanMetric({ value, label, color }) {
  const colors = {
    lucky: "from-lucky-50 to-lucky-100 border-lucky-200 text-lucky-600",
    ticket: "from-ticket-50 to-ticket-100 border-ticket-200 text-ticket-600",
    prize: "from-prize-50 to-prize-100 border-prize-200 text-prize-600"
  };
  return /* @__PURE__ */ jsxs("div", { className: `bg-gradient-to-br ${colors[color]} rounded-xl p-2.5 sm:p-3 text-center border`, children: [
    /* @__PURE__ */ jsx("p", { className: "text-xl sm:text-2xl font-bold", children: value }),
    /* @__PURE__ */ jsx("p", { className: "text-xs font-medium opacity-80 mt-0.5", children: label })
  ] });
}
function StatCard({ label, value, icon, color }) {
  const colorMap = {
    lucky: "from-lucky-100 to-lucky-200/80 text-lucky-700 border-lucky-300",
    green: "from-green-100 to-green-200/80 text-green-700 border-green-300",
    emerald: "from-emerald-100 to-emerald-200/80 text-emerald-700 border-emerald-300",
    amber: "from-amber-100 to-amber-200/80 text-amber-700 border-amber-300",
    rose: "from-rose-100 to-rose-200/80 text-rose-700 border-rose-300"
  };
  return /* @__PURE__ */ jsxs("div", { className: `rounded-2xl p-3 sm:p-4 text-center bg-gradient-to-br border-2 border-dashed shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 ${colorMap[color]}`, children: [
    /* @__PURE__ */ jsx("div", { className: "text-xl sm:text-2xl mb-1", children: icon }),
    /* @__PURE__ */ jsx("p", { className: "text-xl sm:text-2xl font-bold leading-tight", children: value }),
    /* @__PURE__ */ jsx("p", { className: "text-xs mt-1 font-medium opacity-80", children: label })
  ] });
}
function StampCountdown({ editableUntil, showLabel = false }) {
  const [remaining, setRemaining] = useState("");
  useEffect(() => {
    const update = () => {
      const diff = new Date(editableUntil) - /* @__PURE__ */ new Date();
      if (diff <= 0) {
        setRemaining("Expired");
        return;
      }
      const mins = Math.floor(diff / 6e4);
      const secs = Math.floor(diff % 6e4 / 1e3);
      setRemaining(`${mins}:${String(secs).padStart(2, "0")}`);
    };
    update();
    const timer = setInterval(update, 1e3);
    return () => clearInterval(timer);
  }, [editableUntil]);
  const isExpired = remaining === "Expired";
  return /* @__PURE__ */ jsxs("span", { className: `inline-flex items-center gap-1 text-xs font-mono ${isExpired ? "text-red-500" : "text-amber-600"}`, children: [
    showLabel && /* @__PURE__ */ jsx("span", { className: "font-sans font-medium", children: "Time left:" }),
    /* @__PURE__ */ jsxs("span", { children: [
      "⏱️ ",
      remaining
    ] })
  ] });
}
const __vite_glob_0_10 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Dashboard
}, Symbol.toStringTag, { value: "Module" }));
function LinkQr({ auth, locations }) {
  const { data, setData, post, processing, errors, recentlySuccessful, reset } = useForm({
    unique_code: "",
    merchant_location_id: ""
  });
  const submit = (e2) => {
    e2.preventDefault();
    post(route("executive.qr.link"), {
      onSuccess: () => reset("unique_code")
    });
  };
  return /* @__PURE__ */ jsxs(
    AuthenticatedLayout,
    {
      user: auth.user,
      header: /* @__PURE__ */ jsx("h2", { className: "font-semibold text-xl text-gray-800 leading-tight", children: "Link QR Sticker" }),
      children: [
        /* @__PURE__ */ jsx(Head_default, { title: "Link QR Sticker" }),
        /* @__PURE__ */ jsx("div", { className: "py-12", children: /* @__PURE__ */ jsx("div", { className: "max-w-7xl mx-auto sm:px-6 lg:px-8", children: /* @__PURE__ */ jsx("div", { className: "bg-white overflow-hidden shadow-sm sm:rounded-lg", children: /* @__PURE__ */ jsxs("div", { className: "p-6 text-gray-900", children: [
          /* @__PURE__ */ jsxs("header", { children: [
            /* @__PURE__ */ jsx("h2", { className: "text-lg font-medium text-gray-900", children: "Link a Physical QR Code" }),
            /* @__PURE__ */ jsx("p", { className: "mt-1 text-sm text-gray-600", children: "Enter the code printed on the Kutoot sticker and select the merchant location to link it." })
          ] }),
          /* @__PURE__ */ jsxs("form", { onSubmit: submit, className: "mt-6 space-y-6 max-w-xl", children: [
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx(InputLabel, { htmlFor: "unique_code", value: "Sticker Code (e.g. KUT-0001)" }),
              /* @__PURE__ */ jsx(
                TextInput,
                {
                  id: "unique_code",
                  className: "mt-1 block w-full uppercase",
                  value: data.unique_code,
                  onChange: (e2) => setData("unique_code", e2.target.value),
                  required: true,
                  autoFocus: true,
                  placeholder: "KUT-XXXX"
                }
              ),
              /* @__PURE__ */ jsx(InputError, { className: "mt-2", message: errors.unique_code })
            ] }),
            /* @__PURE__ */ jsxs("div", { children: [
              /* @__PURE__ */ jsx(InputLabel, { htmlFor: "merchant_location_id", value: "Merchant Location" }),
              /* @__PURE__ */ jsxs(
                "select",
                {
                  id: "merchant_location_id",
                  className: "mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm",
                  value: data.merchant_location_id,
                  onChange: (e2) => setData("merchant_location_id", e2.target.value),
                  required: true,
                  children: [
                    /* @__PURE__ */ jsx("option", { value: "", children: "Select a location" }),
                    locations.map((loc) => /* @__PURE__ */ jsx("option", { value: loc.id, children: loc.name }, loc.id))
                  ]
                }
              ),
              /* @__PURE__ */ jsx(InputError, { className: "mt-2", message: errors.merchant_location_id })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-4", children: [
              /* @__PURE__ */ jsx(PrimaryButton, { disabled: processing, children: "Link QR Code" }),
              /* @__PURE__ */ jsx(
                Transition,
                {
                  show: recentlySuccessful,
                  enter: "transition ease-in-out",
                  enterFrom: "opacity-0",
                  leave: "transition ease-in-out",
                  leaveTo: "opacity-0",
                  children: /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-600", children: "Linked successfully." })
                }
              )
            ] })
          ] })
        ] }) }) }) })
      ]
    }
  );
}
const __vite_glob_0_11 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: LinkQr
}, Symbol.toStringTag, { value: "Module" }));
function DangerButton({
  className = "",
  disabled,
  children,
  ...props
}) {
  return /* @__PURE__ */ jsx(
    "button",
    {
      ...props,
      className: `inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-700 ${disabled && "opacity-25"} ` + className,
      disabled,
      children
    }
  );
}
function DeleteUserForm({ className = "" }) {
  const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);
  const passwordInput = useRef();
  const {
    data,
    setData,
    delete: destroy,
    processing,
    reset,
    errors,
    clearErrors
  } = useForm({
    password: ""
  });
  const confirmUserDeletion = () => {
    setConfirmingUserDeletion(true);
  };
  const deleteUser = (e2) => {
    e2.preventDefault();
    destroy(route("profile.destroy"), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
      onError: () => passwordInput.current.focus(),
      onFinish: () => reset()
    });
  };
  const closeModal = () => {
    setConfirmingUserDeletion(false);
    clearErrors();
    reset();
  };
  return /* @__PURE__ */ jsxs("section", { className: `space-y-6 ${className}`, children: [
    /* @__PURE__ */ jsxs("header", { children: [
      /* @__PURE__ */ jsx("h2", { className: "text-lg font-medium text-gray-900", children: "Delete Account" }),
      /* @__PURE__ */ jsx("p", { className: "mt-1 text-sm text-gray-600", children: "Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain." })
    ] }),
    /* @__PURE__ */ jsx(DangerButton, { onClick: confirmUserDeletion, children: "Delete Account" }),
    /* @__PURE__ */ jsx(Modal, { show: confirmingUserDeletion, onClose: closeModal, children: /* @__PURE__ */ jsxs("form", { onSubmit: deleteUser, className: "p-6", children: [
      /* @__PURE__ */ jsx("h2", { className: "text-lg font-medium text-gray-900", children: "Are you sure you want to delete your account?" }),
      /* @__PURE__ */ jsx("p", { className: "mt-1 text-sm text-gray-600", children: "Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account." }),
      /* @__PURE__ */ jsxs("div", { className: "mt-6", children: [
        /* @__PURE__ */ jsx(
          InputLabel,
          {
            htmlFor: "password",
            value: "Password",
            className: "sr-only"
          }
        ),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "password",
            type: "password",
            name: "password",
            ref: passwordInput,
            value: data.password,
            onChange: (e2) => setData("password", e2.target.value),
            className: "mt-1 block w-3/4",
            isFocused: true,
            placeholder: "Password"
          }
        ),
        /* @__PURE__ */ jsx(
          InputError,
          {
            message: errors.password,
            className: "mt-2"
          }
        )
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "mt-6 flex justify-end", children: [
        /* @__PURE__ */ jsx(SecondaryButton, { onClick: closeModal, children: "Cancel" }),
        /* @__PURE__ */ jsx(DangerButton, { className: "ms-3", disabled: processing, children: "Delete Account" })
      ] })
    ] }) })
  ] });
}
const __vite_glob_0_13 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: DeleteUserForm
}, Symbol.toStringTag, { value: "Module" }));
function UpdatePasswordForm({ className = "" }) {
  const passwordInput = useRef();
  const currentPasswordInput = useRef();
  const {
    data,
    setData,
    errors,
    put,
    reset,
    processing,
    recentlySuccessful
  } = useForm({
    current_password: "",
    password: "",
    password_confirmation: ""
  });
  const updatePassword = (e2) => {
    e2.preventDefault();
    put(route("password.update"), {
      preserveScroll: true,
      onSuccess: () => reset(),
      onError: (errors2) => {
        if (errors2.password) {
          reset("password", "password_confirmation");
          passwordInput.current.focus();
        }
        if (errors2.current_password) {
          reset("current_password");
          currentPasswordInput.current.focus();
        }
      }
    });
  };
  return /* @__PURE__ */ jsxs("section", { className, children: [
    /* @__PURE__ */ jsxs("header", { children: [
      /* @__PURE__ */ jsx("h2", { className: "text-lg font-medium text-gray-900", children: "Update Password" }),
      /* @__PURE__ */ jsx("p", { className: "mt-1 text-sm text-gray-600", children: "Ensure your account is using a long, random password to stay secure." })
    ] }),
    /* @__PURE__ */ jsxs("form", { onSubmit: updatePassword, className: "mt-6 space-y-6", children: [
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx(
          InputLabel,
          {
            htmlFor: "current_password",
            value: "Current Password"
          }
        ),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "current_password",
            ref: currentPasswordInput,
            value: data.current_password,
            onChange: (e2) => setData("current_password", e2.target.value),
            type: "password",
            className: "mt-1 block w-full",
            autoComplete: "current-password"
          }
        ),
        /* @__PURE__ */ jsx(
          InputError,
          {
            message: errors.current_password,
            className: "mt-2"
          }
        )
      ] }),
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "password", value: "New Password" }),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "password",
            ref: passwordInput,
            value: data.password,
            onChange: (e2) => setData("password", e2.target.value),
            type: "password",
            className: "mt-1 block w-full",
            autoComplete: "new-password"
          }
        ),
        /* @__PURE__ */ jsx(InputError, { message: errors.password, className: "mt-2" })
      ] }),
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx(
          InputLabel,
          {
            htmlFor: "password_confirmation",
            value: "Confirm Password"
          }
        ),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "password_confirmation",
            value: data.password_confirmation,
            onChange: (e2) => setData("password_confirmation", e2.target.value),
            type: "password",
            className: "mt-1 block w-full",
            autoComplete: "new-password"
          }
        ),
        /* @__PURE__ */ jsx(
          InputError,
          {
            message: errors.password_confirmation,
            className: "mt-2"
          }
        )
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-4", children: [
        /* @__PURE__ */ jsx(PrimaryButton, { disabled: processing, children: "Save" }),
        /* @__PURE__ */ jsx(
          Transition,
          {
            show: recentlySuccessful,
            enter: "transition ease-in-out",
            enterFrom: "opacity-0",
            leave: "transition ease-in-out",
            leaveTo: "opacity-0",
            children: /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-600", children: "Saved." })
          }
        )
      ] })
    ] })
  ] });
}
const __vite_glob_0_14 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: UpdatePasswordForm
}, Symbol.toStringTag, { value: "Module" }));
function UpdateProfileInformation({
  mustVerifyEmail,
  status,
  className = ""
}) {
  const user = usePage().props.auth.user;
  const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
    name: user.name,
    email: user.email
  });
  const submit = (e2) => {
    e2.preventDefault();
    patch(route("profile.update"));
  };
  return /* @__PURE__ */ jsxs("section", { className, children: [
    /* @__PURE__ */ jsxs("header", { children: [
      /* @__PURE__ */ jsx("h2", { className: "text-lg font-medium text-gray-900", children: "Profile Information" }),
      /* @__PURE__ */ jsx("p", { className: "mt-1 text-sm text-gray-600", children: "Update your account's profile information and email address." })
    ] }),
    /* @__PURE__ */ jsxs("form", { onSubmit: submit, className: "mt-6 space-y-6", children: [
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "name", value: "Name" }),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "name",
            className: "mt-1 block w-full",
            value: data.name,
            onChange: (e2) => setData("name", e2.target.value),
            required: true,
            isFocused: true,
            autoComplete: "name"
          }
        ),
        /* @__PURE__ */ jsx(InputError, { className: "mt-2", message: errors.name })
      ] }),
      /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsx(InputLabel, { htmlFor: "email", value: "Email" }),
        /* @__PURE__ */ jsx(
          TextInput,
          {
            id: "email",
            type: "email",
            className: "mt-1 block w-full",
            value: data.email,
            onChange: (e2) => setData("email", e2.target.value),
            required: true,
            autoComplete: "username"
          }
        ),
        /* @__PURE__ */ jsx(InputError, { className: "mt-2", message: errors.email })
      ] }),
      mustVerifyEmail && user.email_verified_at === null && /* @__PURE__ */ jsxs("div", { children: [
        /* @__PURE__ */ jsxs("p", { className: "mt-2 text-sm text-gray-800", children: [
          "Your email address is unverified.",
          /* @__PURE__ */ jsx(
            Link_default,
            {
              href: route("verification.send"),
              method: "post",
              as: "button",
              className: "rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2",
              children: "Click here to re-send the verification email."
            }
          )
        ] }),
        status === "verification-link-sent" && /* @__PURE__ */ jsx("div", { className: "mt-2 text-sm font-medium text-green-600", children: "A new verification link has been sent to your email address." })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-4", children: [
        /* @__PURE__ */ jsx(PrimaryButton, { disabled: processing, children: "Save" }),
        /* @__PURE__ */ jsx(
          Transition,
          {
            show: recentlySuccessful,
            enter: "transition ease-in-out",
            enterFrom: "opacity-0",
            leave: "transition ease-in-out",
            leaveTo: "opacity-0",
            children: /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-600", children: "Saved." })
          }
        )
      ] })
    ] })
  ] });
}
const __vite_glob_0_15 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: UpdateProfileInformation
}, Symbol.toStringTag, { value: "Module" }));
function Edit({ mustVerifyEmail, status }) {
  return /* @__PURE__ */ jsxs(
    AuthenticatedLayout,
    {
      header: /* @__PURE__ */ jsx("h2", { className: "text-xl font-semibold leading-tight text-gray-800", children: "Profile" }),
      children: [
        /* @__PURE__ */ jsx(Head_default, { title: "Profile" }),
        /* @__PURE__ */ jsx("div", { className: "py-12", children: /* @__PURE__ */ jsxs("div", { className: "mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8", children: [
          /* @__PURE__ */ jsx("div", { className: "bg-white p-4 shadow sm:rounded-lg sm:p-8", children: /* @__PURE__ */ jsx(
            UpdateProfileInformation,
            {
              mustVerifyEmail,
              status,
              className: "max-w-xl"
            }
          ) }),
          /* @__PURE__ */ jsx("div", { className: "bg-white p-4 shadow sm:rounded-lg sm:p-8", children: /* @__PURE__ */ jsx(UpdatePasswordForm, { className: "max-w-xl" }) }),
          /* @__PURE__ */ jsx("div", { className: "bg-white p-4 shadow sm:rounded-lg sm:p-8", children: /* @__PURE__ */ jsx(DeleteUserForm, { className: "max-w-xl" }) })
        ] }) })
      ]
    }
  );
}
const __vite_glob_0_12 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Edit
}, Symbol.toStringTag, { value: "Module" }));
function useRazorpay({
  isProduction = true,
  user = {},
  appName = "Kutoot",
  themeColor = "#f08c10",
  onSuccess = null,
  onError = null,
  onClose = null
} = {}) {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const [paymentStatus, setPaymentStatus] = useState("idle");
  useEffect(() => {
    if (!isProduction) return;
    const existingScript = document.querySelector('script[src="https://checkout.razorpay.com/v1/checkout.js"]');
    if (existingScript) return;
    const script = document.createElement("script");
    script.src = "https://checkout.razorpay.com/v1/checkout.js";
    script.async = true;
    document.body.appendChild(script);
    return () => {
      if (document.body.contains(script)) {
        document.body.removeChild(script);
      }
    };
  }, [isProduction]);
  const resetState = useCallback(() => {
    setIsLoading(false);
    setError(null);
    setPaymentStatus("idle");
  }, []);
  const openCheckout = useCallback((order, verifyRoute, extraData = {}, description = "Payment") => {
    if (!window.Razorpay) {
      const err = "Razorpay SDK not loaded. Please refresh the page.";
      setError(err);
      setPaymentStatus("failed");
      onError?.(err);
      return;
    }
    setPaymentStatus("checkout_open");
    const options = {
      key: order.key,
      amount: order.amount,
      currency: order.currency,
      name: appName,
      description,
      order_id: order.id,
      handler: function(response) {
        setIsLoading(true);
        setPaymentStatus("verifying");
        router3.post(verifyRoute, {
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_order_id: response.razorpay_order_id,
          razorpay_signature: response.razorpay_signature,
          ...extraData
        }, {
          onSuccess: () => {
            setIsLoading(false);
            setPaymentStatus("success");
            onSuccess?.();
          },
          onError: (errs) => {
            setIsLoading(false);
            setPaymentStatus("failed");
            setError("Payment verification failed. Please contact support.");
            onError?.(errs);
          }
        });
      },
      prefill: {
        name: user?.name || "",
        email: user?.email || ""
      },
      theme: {
        color: themeColor
      },
      modal: {
        ondismiss: () => {
          setIsLoading(false);
          setPaymentStatus("idle");
          onClose?.();
        }
      }
    };
    const rzp = new window.Razorpay(options);
    rzp.on("payment.failed", function(response) {
      setPaymentStatus("failed");
      setError(response.error?.description || "Payment failed. Please try again.");
      onError?.(response.error);
    });
    rzp.open();
  }, [appName, themeColor, user, onSuccess, onError, onClose]);
  const initiatePayment = useCallback(async ({
    orderRoute,
    orderData,
    verifyRoute,
    extraVerifyData = {},
    description = "Payment",
    onDebugSuccess = null
  }) => {
    setIsLoading(true);
    setError(null);
    setPaymentStatus("initiating");
    if (!isProduction) {
      router3.post(orderRoute, orderData, {
        onSuccess: () => {
          setIsLoading(false);
          setPaymentStatus("success");
          onDebugSuccess?.();
        },
        onError: (errs) => {
          setIsLoading(false);
          setPaymentStatus("failed");
          setError("Request failed");
          onError?.(errs);
        }
      });
      return;
    }
    try {
      const response = await fetch(orderRoute, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || ""
        },
        body: JSON.stringify(orderData)
      });
      const result = await response.json();
      if (!response.ok) {
        const errMsg = result.error || "Something went wrong";
        setError(errMsg);
        setIsLoading(false);
        setPaymentStatus("failed");
        onError?.(errMsg);
        return;
      }
      setIsLoading(false);
      openCheckout(result.order, verifyRoute, extraVerifyData, description);
    } catch (err) {
      setIsLoading(false);
      const errMsg = "Payment initiation failed. Please try again.";
      setError(errMsg);
      setPaymentStatus("failed");
      onError?.(errMsg);
    }
  }, [isProduction, openCheckout, onError]);
  return {
    initiatePayment,
    openCheckout,
    isLoading,
    error,
    paymentStatus,
    resetState,
    clearError: () => setError(null)
  };
}
function Index({ auth, plans, currentSubscription, primaryCampaignId, availableCampaigns, isLoggedIn }) {
  const currentPlanIndex = plans.findIndex((p2) => p2.id === currentSubscription?.plan_id);
  const { flash, appDebug } = usePage().props;
  const [showCampaignModal, setShowCampaignModal] = useState(false);
  const [selectedCampaign, setSelectedCampaign] = useState(null);
  const [upgradingPlanId, setUpgradingPlanId] = useState(null);
  const [showSuccess, setShowSuccess] = useState(false);
  const [successDetails, setSuccessDetails] = useState(null);
  useRazorpay({
    isProduction: !appDebug,
    user: auth.user
  });
  useEffect(() => {
    if (flash?.needsCampaignSelection) {
      setShowCampaignModal(true);
    }
    if (flash?.success) {
      setShowSuccess(true);
      setSuccessDetails({ message: flash.success });
    }
  }, [flash?.needsCampaignSelection, flash?.success]);
  const handleUpgrade = async (plan) => {
    if (upgradingPlanId) return;
    if (plan.price <= 0 || appDebug) {
      setUpgradingPlanId(plan.id);
      router3.post(route("subscriptions.upgrade"), { plan_id: plan.id }, {
        onFinish: () => setUpgradingPlanId(null)
      });
      return;
    }
    setUpgradingPlanId(plan.id);
    try {
      const response = await fetch(route("subscriptions.upgrade"), {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || ""
        },
        body: JSON.stringify({ plan_id: plan.id })
      });
      const result = await response.json();
      if (!response.ok) {
        alert(result.error || "Something went wrong");
        setUpgradingPlanId(null);
        return;
      }
      const { order, transaction_id, plan_id } = result;
      const options = {
        key: order.key,
        amount: order.amount,
        currency: order.currency,
        name: "Kutoot",
        description: `Upgrade to ${plan.name}`,
        order_id: order.id,
        handler: function(response2) {
          router3.post(route("subscriptions.verify-payment", transaction_id), {
            razorpay_payment_id: response2.razorpay_payment_id,
            razorpay_order_id: response2.razorpay_order_id,
            razorpay_signature: response2.razorpay_signature,
            plan_id
          }, {
            onFinish: () => setUpgradingPlanId(null)
          });
        },
        prefill: {
          name: auth.user?.name || "",
          email: auth.user?.email || ""
        },
        theme: { color: "#f08c10" },
        modal: {
          ondismiss: () => setUpgradingPlanId(null)
        }
      };
      const rzp = new window.Razorpay(options);
      rzp.on("payment.failed", () => setUpgradingPlanId(null));
      rzp.open();
    } catch (error) {
      console.error("Payment initiation failed", error);
      alert("Payment initiation failed. Please try again.");
      setUpgradingPlanId(null);
    }
  };
  const handleCampaignSelect = () => {
    if (!selectedCampaign) return;
    router3.post(route("subscriptions.setPrimaryCampaign"), { campaign_id: selectedCampaign }, {
      onSuccess: () => setShowCampaignModal(false)
    });
  };
  const tierConfig = [
    { bg: "from-lucky-50 to-lucky-100", accent: "text-lucky-600", border: "border-lucky-300", badge: "bg-lucky-100 text-lucky-700", icon: "🎫", ring: "ring-lucky-200" },
    { bg: "from-ticket-50 to-ticket-100", accent: "text-ticket-600", border: "border-ticket-300", badge: "bg-ticket-100 text-ticket-700", icon: "⭐", ring: "ring-ticket-200", popular: true },
    { bg: "from-yellow-50 to-amber-100", accent: "text-amber-600", border: "border-yellow-400", badge: "bg-yellow-100 text-amber-700", icon: "👑", ring: "ring-yellow-200" }
  ];
  return /* @__PURE__ */ jsxs(
    AuthenticatedLayout,
    {
      user: auth.user,
      header: /* @__PURE__ */ jsx("h2", { className: "text-xl font-bold leading-tight text-white flex items-center gap-2", children: "⭐ Subscription Plans" }),
      children: [
        /* @__PURE__ */ jsx(Head_default, { title: "Subscriptions" }),
        /* @__PURE__ */ jsx("div", { className: "py-6 sm:py-8", children: /* @__PURE__ */ jsxs("div", { className: "max-w-7xl mx-auto px-4 sm:px-6 lg:px-8", children: [
          /* @__PURE__ */ jsx("div", { className: "text-center mb-8", children: /* @__PURE__ */ jsx("p", { className: "text-gray-500 text-sm max-w-lg mx-auto", children: "Choose the plan that fits your needs. Upgrade anytime to unlock more discounts, stamps, and rewards." }) }),
          /* @__PURE__ */ jsx("div", { className: "grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6", children: plans.map((plan, index) => {
            const tier = tierConfig[index % tierConfig.length];
            const isCurrent = currentSubscription?.plan_id === plan.id;
            const isUpgradable = isLoggedIn && !isCurrent && !plan.is_default && index > currentPlanIndex;
            const isUpgrading = upgradingPlanId === plan.id;
            return /* @__PURE__ */ jsxs(
              "div",
              {
                className: `coupon-card overflow-visible transition-all duration-300 transform hover:-translate-y-2 hover:shadow-xl relative
                                        ${isCurrent ? `${tier.border} ring-2 ${tier.ring} shadow-lg` : ""}
                                        ${tier.popular && !isCurrent ? "md:-translate-y-2" : ""}
                                    `,
                children: [
                  tier.popular && !isCurrent && /* @__PURE__ */ jsx("div", { className: "absolute -top-3 left-1/2 -translate-x-1/2 z-10", children: /* @__PURE__ */ jsx("span", { className: "bg-gradient-to-r from-ticket-500 to-ticket-600 text-white px-4 py-1 rounded-full text-xs font-bold shadow-md whitespace-nowrap", children: "🔥 BEST VALUE" }) }),
                  isCurrent && /* @__PURE__ */ jsx("div", { className: "absolute -top-3 left-1/2 -translate-x-1/2 z-10", children: /* @__PURE__ */ jsx("span", { className: "golden-badge px-4 py-1 rounded-full text-xs whitespace-nowrap", children: "⭐ CURRENT PLAN" }) }),
                  /* @__PURE__ */ jsxs("div", { className: `p-5 sm:p-6 bg-gradient-to-br ${tier.bg} rounded-t-2xl text-center`, children: [
                    /* @__PURE__ */ jsx("span", { className: "text-4xl block mb-2", children: tier.icon }),
                    /* @__PURE__ */ jsx("h3", { className: "font-display text-xl sm:text-2xl text-gray-900 mb-1", children: plan.name }),
                    /* @__PURE__ */ jsx("div", { className: "mb-4", children: plan.price > 0 ? /* @__PURE__ */ jsxs("span", { className: `text-3xl font-bold ${tier.accent}`, children: [
                      /* @__PURE__ */ jsx(CurrencySymbol, {}),
                      plan.price.toFixed(0)
                    ] }) : /* @__PURE__ */ jsx("span", { className: "text-lg font-medium text-gray-400", children: "Free" }) }),
                    /* @__PURE__ */ jsxs("div", { className: "flex gap-2 sm:gap-3", children: [
                      /* @__PURE__ */ jsxs("div", { className: "flex-1 bg-white/80 backdrop-blur-sm rounded-xl p-2.5 sm:p-3 text-center border border-dashed border-lucky-200", children: [
                        /* @__PURE__ */ jsx("p", { className: `text-xl sm:text-2xl font-bold ${tier.accent}`, children: plan.stamps_on_purchase }),
                        /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-500 font-medium", children: "🎫 Bonus" })
                      ] }),
                      /* @__PURE__ */ jsxs("div", { className: "flex-1 bg-white/80 backdrop-blur-sm rounded-xl p-2.5 sm:p-3 text-center border border-dashed border-lucky-200", children: [
                        /* @__PURE__ */ jsx("p", { className: `text-xl sm:text-2xl font-bold ${tier.accent}`, children: plan.stamps_per_100 }),
                        /* @__PURE__ */ jsxs("p", { className: "text-xs text-gray-500 font-medium", children: [
                          "Per ",
                          /* @__PURE__ */ jsx(CurrencySymbol, {}),
                          "100"
                        ] })
                      ] })
                    ] })
                  ] }),
                  /* @__PURE__ */ jsx("div", { className: "flex justify-center gap-1.5 py-1.5 bg-gradient-to-r from-transparent via-lucky-50 to-transparent", children: [...Array(12)].map((_, i2) => /* @__PURE__ */ jsx("div", { className: "w-1.5 h-1.5 rounded-full bg-lucky-200" }, i2)) }),
                  /* @__PURE__ */ jsxs("div", { className: "p-5 sm:p-6", children: [
                    /* @__PURE__ */ jsxs("ul", { className: "text-sm text-gray-600 space-y-3 mb-6", children: [
                      /* @__PURE__ */ jsx(FeatureRow, { icon: "🎟️", label: "Max Discounted Bills", value: plan.max_discounted_bills }),
                      /* @__PURE__ */ jsx(FeatureRow, { icon: "💰", label: "Max Redeemable", value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
                        /* @__PURE__ */ jsx(CurrencySymbol, {}),
                        parseFloat(plan.max_redeemable_amount).toFixed(0)
                      ] }) }),
                      /* @__PURE__ */ jsx(FeatureRow, { icon: "⏳", label: "Validity", value: plan.duration_days ? `${plan.duration_days} days` : "∞", last: true })
                    ] }),
                    isCurrent ? /* @__PURE__ */ jsxs("div", { children: [
                      /* @__PURE__ */ jsxs("button", { disabled: true, className: "w-full golden-badge py-2.5 px-4 rounded-full cursor-default text-sm flex items-center justify-center gap-1.5", children: [
                        /* @__PURE__ */ jsx("svg", { className: "w-4 h-4", fill: "currentColor", viewBox: "0 0 20 20", children: /* @__PURE__ */ jsx("path", { fillRule: "evenodd", d: "M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z", clipRule: "evenodd" }) }),
                        "Current Plan"
                      ] }),
                      currentSubscription.expires_at && /* @__PURE__ */ jsxs("p", { className: "text-center text-xs text-gray-400 mt-2 bg-gray-50 rounded-full py-1.5 px-3", children: [
                        "⏳ Expires: ",
                        /* @__PURE__ */ jsx("span", { className: "font-bold", children: currentSubscription.expires_at })
                      ] })
                    ] }) : plan.is_default ? /* @__PURE__ */ jsx("p", { className: "w-full text-center text-xs text-gray-400 py-2.5 bg-gray-50 rounded-full", children: "Auto-assigned on signup" }) : !isLoggedIn ? /* @__PURE__ */ jsx(
                      Link_default,
                      {
                        href: route("login"),
                        className: "w-full block text-center lucky-gradient text-white font-bold py-2.5 px-4 rounded-full transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 text-sm",
                        children: "🔑 Login to Upgrade"
                      }
                    ) : isUpgradable ? /* @__PURE__ */ jsx(
                      "button",
                      {
                        onClick: () => handleUpgrade(plan),
                        disabled: isUpgrading,
                        className: "w-full lucky-gradient text-white font-bold py-2.5 px-4 rounded-full transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 text-sm disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2",
                        children: isUpgrading ? /* @__PURE__ */ jsxs(Fragment$1, { children: [
                          /* @__PURE__ */ jsxs("svg", { className: "animate-spin h-4 w-4", viewBox: "0 0 24 24", children: [
                            /* @__PURE__ */ jsx("circle", { className: "opacity-25", cx: "12", cy: "12", r: "10", stroke: "currentColor", strokeWidth: "4", fill: "none" }),
                            /* @__PURE__ */ jsx("path", { className: "opacity-75", fill: "currentColor", d: "M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" })
                          ] }),
                          "Processing..."
                        ] }) : "🚀 Upgrade"
                      }
                    ) : /* @__PURE__ */ jsx("p", { className: "w-full text-center text-xs text-gray-400 py-2.5 bg-gray-50 rounded-full", children: "Lower tier" })
                  ] })
                ]
              },
              plan.id
            );
          }) }),
          !isLoggedIn && /* @__PURE__ */ jsx("div", { className: "mt-10 text-center", children: /* @__PURE__ */ jsxs("div", { className: "inline-block coupon-card p-6 sm:p-8", children: [
            /* @__PURE__ */ jsx("span", { className: "text-3xl block mb-2", children: "🔑" }),
            /* @__PURE__ */ jsx("h3", { className: "font-display text-lg text-gray-900 mb-1", children: "Sign up to unlock all plans" }),
            /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 mb-4 max-w-sm", children: "Create a free account to upgrade your plan, earn stamps, and access exclusive discounts." }),
            /* @__PURE__ */ jsx(
              Link_default,
              {
                href: route("login"),
                className: "inline-flex items-center gap-2 lucky-gradient text-white font-bold py-2.5 px-6 rounded-full shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all text-sm",
                children: "Get Started Free"
              }
            )
          ] }) })
        ] }) }),
        showCampaignModal && availableCampaigns.length > 0 && /* @__PURE__ */ jsx("div", { className: "fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4", children: /* @__PURE__ */ jsxs("div", { className: "coupon-card w-full max-w-md p-6", children: [
          /* @__PURE__ */ jsxs("h3", { className: "font-display text-xl text-gray-900 mb-2 flex items-center gap-2", children: [
            /* @__PURE__ */ jsx("span", { className: "text-2xl", children: "🎯" }),
            " Select Your Campaign"
          ] }),
          /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 mb-5", children: "Choose the campaign you'd like to collect stamps for:" }),
          /* @__PURE__ */ jsx("div", { className: "space-y-2 mb-6 max-h-60 overflow-y-auto", children: availableCampaigns.map((campaign) => /* @__PURE__ */ jsxs(
            "label",
            {
              className: `flex items-center gap-3 p-3 rounded-xl border-2 border-dashed cursor-pointer transition-all ${selectedCampaign === campaign.id ? "border-lucky-400 bg-lucky-50" : "border-gray-200 hover:border-lucky-200 hover:bg-lucky-50/30"}`,
              children: [
                /* @__PURE__ */ jsx(
                  "input",
                  {
                    type: "radio",
                    name: "campaign",
                    value: campaign.id,
                    checked: selectedCampaign === campaign.id,
                    onChange: () => setSelectedCampaign(campaign.id),
                    className: "text-lucky-500 focus:ring-lucky-400"
                  }
                ),
                /* @__PURE__ */ jsx("span", { className: "font-medium text-gray-900", children: campaign.reward_name })
              ]
            },
            campaign.id
          )) }),
          /* @__PURE__ */ jsx(
            "button",
            {
              onClick: handleCampaignSelect,
              disabled: !selectedCampaign,
              className: `w-full font-bold py-2.5 px-4 rounded-full transition-all text-sm ${selectedCampaign ? "lucky-gradient text-white shadow-md hover:shadow-lg transform hover:-translate-y-0.5" : "bg-gray-200 text-gray-400 cursor-not-allowed"}`,
              children: "✅ Confirm Campaign"
            }
          )
        ] }) }),
        /* @__PURE__ */ jsx(
          ConfirmationModal,
          {
            show: showSuccess,
            onClose: () => {
              setShowSuccess(false);
              setSuccessDetails(null);
            },
            title: "Plan Upgraded!",
            message: successDetails?.message
          }
        )
      ]
    }
  );
}
function FeatureRow({ icon, label, value, last = false }) {
  return /* @__PURE__ */ jsxs("li", { className: `flex justify-between py-1.5 ${!last ? "border-b border-dashed border-lucky-100" : ""}`, children: [
    /* @__PURE__ */ jsxs("span", { className: "text-gray-500 flex items-center gap-1.5", children: [
      /* @__PURE__ */ jsx("span", { children: icon }),
      " ",
      label
    ] }),
    /* @__PURE__ */ jsx("span", { className: "font-bold text-gray-900", children: value })
  ] });
}
const __vite_glob_0_16 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Index
}, Symbol.toStringTag, { value: "Module" }));
const statusConfig = {
  paid: { label: "Paid", bg: "bg-green-100", text: "text-green-700", dot: "bg-green-500" },
  completed: { label: "Completed", bg: "bg-emerald-100", text: "text-emerald-700", dot: "bg-emerald-500" },
  pending: { label: "Pending", bg: "bg-amber-100", text: "text-amber-700", dot: "bg-amber-500" },
  failed: { label: "Failed", bg: "bg-red-100", text: "text-red-700", dot: "bg-red-500" },
  refunded: { label: "Refunded", bg: "bg-blue-100", text: "text-blue-700", dot: "bg-blue-500" }
};
function StatusBadge({ status, className = "" }) {
  const key = status?.toLowerCase() || "pending";
  const config2 = statusConfig[key] || statusConfig.pending;
  return /* @__PURE__ */ jsxs("span", { className: `inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold ${config2.bg} ${config2.text} ${className}`, children: [
    /* @__PURE__ */ jsx("span", { className: `w-1.5 h-1.5 rounded-full ${config2.dot}` }),
    config2.label
  ] });
}
function Transactions({ auth, subscriptionTransactions, couponTransactions }) {
  const [activeTab, setActiveTab] = useState("subscriptions");
  const subCount = subscriptionTransactions.total || 0;
  const couponCount = couponTransactions.total || 0;
  return /* @__PURE__ */ jsxs(
    AuthenticatedLayout,
    {
      user: auth.user,
      header: /* @__PURE__ */ jsx("h2", { className: "text-xl font-bold leading-tight text-white flex items-center gap-2", children: "💳 Transactions" }),
      children: [
        /* @__PURE__ */ jsx(Head_default, { title: "Transactions" }),
        /* @__PURE__ */ jsx("div", { className: "py-6 sm:py-8", children: /* @__PURE__ */ jsxs("div", { className: "max-w-5xl mx-auto px-4 sm:px-6 lg:px-8", children: [
          /* @__PURE__ */ jsxs("div", { className: "flex gap-2 mb-6 bg-white/80 backdrop-blur-sm rounded-full p-1.5 border-2 border-dashed border-lucky-200 shadow-sm w-fit", children: [
            /* @__PURE__ */ jsx(
              TabButton,
              {
                active: activeTab === "subscriptions",
                onClick: () => setActiveTab("subscriptions"),
                icon: "🏆",
                label: "Subscriptions",
                count: subCount
              }
            ),
            /* @__PURE__ */ jsx(
              TabButton,
              {
                active: activeTab === "coupons",
                onClick: () => setActiveTab("coupons"),
                icon: "🎫",
                label: "Coupons",
                count: couponCount
              }
            )
          ] }),
          activeTab === "subscriptions" && /* @__PURE__ */ jsx("div", { className: "space-y-3", children: subscriptionTransactions.data.length > 0 ? subscriptionTransactions.data.map((tx) => /* @__PURE__ */ jsx(TransactionCard, { type: "subscription", transaction: tx }, tx.id)) : /* @__PURE__ */ jsx("div", { className: "coupon-card", children: /* @__PURE__ */ jsx(
            EmptyState,
            {
              icon: "🏆",
              title: "No subscription payments yet",
              description: "Upgrade your plan to unlock more coupons and earn bonus stamps.",
              actionLabel: "Browse Plans",
              actionHref: route("subscriptions.index")
            }
          ) }) }),
          activeTab === "coupons" && /* @__PURE__ */ jsx("div", { className: "space-y-3", children: couponTransactions.data.length > 0 ? couponTransactions.data.map((tx) => /* @__PURE__ */ jsx(TransactionCard, { type: "coupon", transaction: tx }, tx.id)) : /* @__PURE__ */ jsx("div", { className: "coupon-card", children: /* @__PURE__ */ jsx(
            EmptyState,
            {
              icon: "🎫",
              title: "No coupon redemptions yet",
              description: "Redeem a coupon at a partner store to see your first transaction here.",
              actionLabel: "Browse Coupons",
              actionHref: route("coupons.index")
            }
          ) }) })
        ] }) })
      ]
    }
  );
}
function TabButton({ active, onClick, icon, label, count }) {
  return /* @__PURE__ */ jsxs(
    "button",
    {
      onClick,
      className: `flex items-center gap-1.5 px-4 sm:px-5 py-2 rounded-full text-sm font-bold transition-all ${active ? "lucky-gradient text-white shadow-md" : "text-gray-500 hover:text-gray-700 hover:bg-lucky-50"}`,
      children: [
        /* @__PURE__ */ jsx("span", { children: icon }),
        /* @__PURE__ */ jsx("span", { className: "hidden sm:inline", children: label }),
        /* @__PURE__ */ jsx("span", { className: `text-xs px-1.5 py-0.5 rounded-full font-bold ${active ? "bg-white/20 text-white" : "bg-gray-200 text-gray-600"}`, children: count })
      ]
    }
  );
}
function TransactionCard({ type, transaction: tx }) {
  const [expanded, setExpanded] = useState(false);
  if (type === "subscription") {
    return /* @__PURE__ */ jsx("div", { className: "coupon-card overflow-hidden hover:shadow-lg transition-all duration-200", children: /* @__PURE__ */ jsxs("div", { className: "p-4 sm:p-5", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start mb-3", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3", children: [
          /* @__PURE__ */ jsx("div", { className: "w-10 h-10 rounded-xl bg-gradient-to-br from-lucky-100 to-lucky-200 flex items-center justify-center flex-shrink-0", children: /* @__PURE__ */ jsx("span", { className: "text-lg", children: "🏆" }) }),
          /* @__PURE__ */ jsxs("div", { children: [
            /* @__PURE__ */ jsx("h3", { className: "font-bold text-gray-900", children: tx.plan_name }),
            /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-400", children: tx.created_at })
          ] })
        ] }),
        /* @__PURE__ */ jsx(StatusBadge, { status: tx.payment_status })
      ] }),
      /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-3 gap-2 sm:gap-3", children: [
        /* @__PURE__ */ jsx(MetricBox, { label: "Amount", value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
          /* @__PURE__ */ jsx(CurrencySymbol, {}),
          tx.amount.toFixed(2)
        ] }) }),
        /* @__PURE__ */ jsx(MetricBox, { label: "GST", value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
          /* @__PURE__ */ jsx(CurrencySymbol, {}),
          tx.gst_amount.toFixed(2)
        ] }) }),
        /* @__PURE__ */ jsx(MetricBox, { label: "Total Paid", value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
          /* @__PURE__ */ jsx(CurrencySymbol, {}),
          tx.total_amount.toFixed(2)
        ] }), highlight: true })
      ] }),
      /* @__PURE__ */ jsxs(
        "button",
        {
          onClick: () => setExpanded(!expanded),
          className: "mt-3 text-xs text-lucky-600 hover:text-lucky-700 font-medium flex items-center gap-1 transition-colors",
          children: [
            expanded ? "Hide" : "Show",
            " details",
            /* @__PURE__ */ jsx("svg", { className: `w-3.5 h-3.5 transition-transform ${expanded ? "rotate-180" : ""}`, fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M19 9l-7 7-7-7" }) })
          ]
        }
      ),
      expanded && /* @__PURE__ */ jsxs("div", { className: "mt-3 bg-lucky-50/50 rounded-xl p-3 space-y-1.5 text-sm border border-lucky-100", children: [
        /* @__PURE__ */ jsx(DetailRow, { label: "Payment Method", value: tx.payment_method }),
        tx.payment_id && /* @__PURE__ */ jsx(DetailRow, { label: "Payment ID", value: tx.payment_id, mono: true })
      ] })
    ] }) });
  }
  return /* @__PURE__ */ jsx("div", { className: "coupon-card overflow-hidden hover:shadow-lg transition-all duration-200", children: /* @__PURE__ */ jsxs("div", { className: "p-4 sm:p-5", children: [
    /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-start mb-3", children: [
      /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-3", children: [
        /* @__PURE__ */ jsx("div", { className: "w-10 h-10 rounded-xl bg-gradient-to-br from-ticket-100 to-ticket-200 flex items-center justify-center flex-shrink-0", children: /* @__PURE__ */ jsx("span", { className: "text-lg", children: "🎫" }) }),
        /* @__PURE__ */ jsxs("div", { className: "min-w-0", children: [
          /* @__PURE__ */ jsx("h3", { className: "font-bold text-gray-900 truncate", children: tx.coupon_title }),
          /* @__PURE__ */ jsxs("p", { className: "text-xs text-gray-500 truncate", children: [
            "📍 ",
            tx.merchant_location
          ] }),
          /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-400", children: tx.created_at })
        ] })
      ] }),
      /* @__PURE__ */ jsx(StatusBadge, { status: tx.payment_status })
    ] }),
    /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-2 sm:grid-cols-4 gap-2", children: [
      /* @__PURE__ */ jsx(MetricBox, { label: "Bill Amount", value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
        /* @__PURE__ */ jsx(CurrencySymbol, {}),
        tx.bill_amount.toFixed(2)
      ] }) }),
      /* @__PURE__ */ jsx(MetricBox, { label: "Discount", value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
        "-",
        /* @__PURE__ */ jsx(CurrencySymbol, {}),
        tx.discount_applied.toFixed(2)
      ] }), valueClass: "text-green-600" }),
      /* @__PURE__ */ jsx(MetricBox, { label: "Fee + GST", value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
        /* @__PURE__ */ jsx(CurrencySymbol, {}),
        (tx.platform_fee + tx.gst_amount).toFixed(2)
      ] }) }),
      /* @__PURE__ */ jsx(MetricBox, { label: "Total Paid", value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
        /* @__PURE__ */ jsx(CurrencySymbol, {}),
        tx.total_paid.toFixed(2)
      ] }), highlight: true })
    ] }),
    /* @__PURE__ */ jsxs(
      "button",
      {
        onClick: () => setExpanded(!expanded),
        className: "mt-3 text-xs text-lucky-600 hover:text-lucky-700 font-medium flex items-center gap-1 transition-colors",
        children: [
          expanded ? "Hide" : "Show",
          " details",
          /* @__PURE__ */ jsx("svg", { className: `w-3.5 h-3.5 transition-transform ${expanded ? "rotate-180" : ""}`, fill: "none", viewBox: "0 0 24 24", stroke: "currentColor", children: /* @__PURE__ */ jsx("path", { strokeLinecap: "round", strokeLinejoin: "round", strokeWidth: "2", d: "M19 9l-7 7-7-7" }) })
        ]
      }
    ),
    expanded && /* @__PURE__ */ jsxs("div", { className: "mt-3 bg-lucky-50/50 rounded-xl p-3 space-y-1.5 text-sm border border-lucky-100", children: [
      /* @__PURE__ */ jsx(DetailRow, { label: "Platform Fee", value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
        /* @__PURE__ */ jsx(CurrencySymbol, {}),
        tx.platform_fee.toFixed(2)
      ] }) }),
      /* @__PURE__ */ jsx(DetailRow, { label: "GST", value: /* @__PURE__ */ jsxs(Fragment$1, { children: [
        /* @__PURE__ */ jsx(CurrencySymbol, {}),
        tx.gst_amount.toFixed(2)
      ] }) }),
      /* @__PURE__ */ jsx(DetailRow, { label: "Payment Method", value: tx.payment_method }),
      tx.payment_id && /* @__PURE__ */ jsx(DetailRow, { label: "Payment ID", value: tx.payment_id, mono: true })
    ] })
  ] }) });
}
function MetricBox({ label, value, highlight = false, valueClass = "" }) {
  return /* @__PURE__ */ jsxs("div", { className: `rounded-xl p-2.5 sm:p-3 ${highlight ? "bg-gradient-to-br from-lucky-50 to-lucky-100 border border-lucky-200" : "bg-gray-50 border border-gray-100"}`, children: [
    /* @__PURE__ */ jsx("p", { className: "text-xs text-gray-400 uppercase tracking-wide font-medium", children: label }),
    /* @__PURE__ */ jsx("p", { className: `text-sm sm:text-base font-bold mt-0.5 ${highlight ? "text-lucky-700" : valueClass || "text-gray-900"}`, children: value })
  ] });
}
function DetailRow({ label, value, mono = false }) {
  return /* @__PURE__ */ jsxs("div", { className: "flex justify-between items-center", children: [
    /* @__PURE__ */ jsx("span", { className: "text-gray-500", children: label }),
    /* @__PURE__ */ jsx("span", { className: `font-medium text-gray-900 ${mono ? "font-mono text-xs" : "capitalize"}`, children: value })
  ] });
}
const __vite_glob_0_17 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Transactions
}, Symbol.toStringTag, { value: "Module" }));
function Welcome({ auth }) {
  return /* @__PURE__ */ jsxs(Fragment$1, { children: [
    /* @__PURE__ */ jsx(Head_default, { title: "Welcome to Kutoot" }),
    /* @__PURE__ */ jsxs("div", { className: "min-h-screen bg-gradient-to-br from-lucky-50 via-white to-ticket-50 overflow-hidden relative", children: [
      /* @__PURE__ */ jsx("div", { className: "absolute top-10 left-10 w-16 h-16 bg-lucky-200 rounded-full opacity-20 animate-float" }),
      /* @__PURE__ */ jsx("div", { className: "absolute top-32 right-20 w-10 h-10 bg-ticket-200 rounded-full opacity-20 animate-float", style: { animationDelay: "0.5s" } }),
      /* @__PURE__ */ jsx("div", { className: "absolute bottom-20 left-1/4 w-12 h-12 bg-yellow-200 rounded-full opacity-20 animate-float", style: { animationDelay: "1s" } }),
      /* @__PURE__ */ jsx("div", { className: "absolute top-1/2 right-10 w-8 h-8 bg-green-200 rounded-full opacity-20 animate-float", style: { animationDelay: "1.5s" } }),
      /* @__PURE__ */ jsx("div", { className: "absolute bottom-40 right-1/3 w-6 h-6 bg-prize-200 rounded-full opacity-20 animate-float", style: { animationDelay: "2s" } }),
      /* @__PURE__ */ jsxs("nav", { className: "relative z-10 flex items-center justify-between px-6 py-4 max-w-7xl mx-auto", children: [
        /* @__PURE__ */ jsx(ApplicationLogo, {}),
        /* @__PURE__ */ jsx("div", { className: "flex items-center gap-3", children: auth.user ? /* @__PURE__ */ jsx(
          Link_default,
          {
            href: route("dashboard"),
            className: "rounded-full px-5 py-2.5 font-bold text-sm text-white lucky-gradient shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all",
            children: "Dashboard"
          }
        ) : /* @__PURE__ */ jsx(
          Link_default,
          {
            href: route("login"),
            className: "rounded-full px-5 py-2.5 font-bold text-sm text-lucky-700 border-2 border-lucky-300 hover:bg-lucky-50 transition-colors",
            children: "Login / Register"
          }
        ) })
      ] }),
      /* @__PURE__ */ jsxs("main", { className: "relative z-10 flex flex-col items-center justify-center px-6 pt-8 sm:pt-12 pb-16 sm:pb-24", children: [
        /* @__PURE__ */ jsxs("div", { className: "relative mb-6", children: [
          /* @__PURE__ */ jsx("div", { className: "w-28 h-28 sm:w-32 sm:h-32 starburst opacity-20 animate-spin-slow" }),
          /* @__PURE__ */ jsx("div", { className: "absolute inset-0 flex items-center justify-center", children: /* @__PURE__ */ jsx("div", { className: "w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-full shadow-xl flex items-center justify-center animate-pulse-glow", children: /* @__PURE__ */ jsx("span", { className: "text-3xl", children: "🎟️" }) }) })
        ] }),
        /* @__PURE__ */ jsx("h1", { className: "font-display text-4xl sm:text-5xl md:text-7xl text-center bg-gradient-to-r from-lucky-600 via-ticket-500 to-lucky-600 bg-clip-text text-transparent mb-3 leading-tight", children: "Win Big with Kutoot!" }),
        /* @__PURE__ */ jsx("p", { className: "text-base sm:text-lg md:text-xl text-gray-500 text-center max-w-xl mb-4", children: "Your favourite merchants, exclusive discounts, and lucky rewards — all in one place." }),
        /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-400 text-center max-w-md mb-8", children: "Collect stamps with every purchase, redeem discount coupons, and stand a chance to win big in campaigns!" }),
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col sm:flex-row justify-center gap-3 sm:gap-4 mb-14 w-full sm:w-auto px-4 sm:px-0", children: [
          !auth.user && /* @__PURE__ */ jsxs(
            Link_default,
            {
              href: route("login"),
              className: "group relative inline-flex items-center justify-center gap-2 rounded-full px-8 py-4 font-bold text-base sm:text-lg text-white lucky-gradient shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all",
              children: [
                /* @__PURE__ */ jsx("span", { className: "text-xl sm:text-2xl group-hover:animate-bounce", children: "🎰" }),
                "Start Winning",
                /* @__PURE__ */ jsx("span", { className: "absolute -top-2 -right-2 golden-badge text-xs px-2 py-0.5 rounded-full", children: "FREE" })
              ]
            }
          ),
          /* @__PURE__ */ jsxs(
            Link_default,
            {
              href: auth.user ? route("campaigns.index") : route("login"),
              className: "inline-flex items-center justify-center gap-2 rounded-full px-8 py-4 font-bold text-base sm:text-lg text-lucky-700 bg-white border-2 border-lucky-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all",
              children: [
                /* @__PURE__ */ jsx("span", { className: "text-xl sm:text-2xl", children: "🏆" }),
                "View Campaigns"
              ]
            }
          )
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "w-full max-w-4xl mb-14", children: [
          /* @__PURE__ */ jsx("h2", { className: "font-display text-2xl text-center text-gray-900 mb-8", children: "How it Works" }),
          /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 md:grid-cols-3 gap-0 md:gap-0 relative", children: [
            /* @__PURE__ */ jsx("div", { className: "hidden md:block absolute top-12 left-[16.667%] right-[16.667%] h-0.5 border-t-2 border-dashed border-lucky-300 z-0" }),
            /* @__PURE__ */ jsx(StepCard, { step: 1, icon: "📱", title: "Sign Up & Choose a Plan", description: "Create your free account and pick a plan that suits you." }),
            /* @__PURE__ */ jsx(StepCard, { step: 2, icon: "🎫", title: "Redeem Coupons", description: "Use exclusive discount coupons at partner stores and pay less." }),
            /* @__PURE__ */ jsx(StepCard, { step: 3, icon: "🏅", title: "Earn Stamps & Win", description: "Collect stamps with every purchase and win campaign rewards!" })
          ] })
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 max-w-5xl w-full px-4 sm:px-0", children: [
          /* @__PURE__ */ jsx(
            FeatureTicket,
            {
              emoji: "🎫",
              title: "Collect Stamps",
              description: "Every purchase earns you stamps. Stack them up for bigger campaign rewards!",
              color: "lucky"
            }
          ),
          /* @__PURE__ */ jsx(
            FeatureTicket,
            {
              emoji: "🎁",
              title: "Exclusive Discounts",
              description: "Unlock coupons with real savings at your favourite local stores.",
              color: "ticket"
            }
          ),
          /* @__PURE__ */ jsx(
            FeatureTicket,
            {
              emoji: "🏅",
              title: "Win Campaigns",
              description: "Your stamps fuel campaigns. Reach the target and win the prize!",
              color: "prize"
            }
          )
        ] }),
        /* @__PURE__ */ jsxs("div", { className: "mt-14 flex flex-wrap items-center justify-center gap-4 sm:gap-6 text-sm text-gray-500 px-4", children: [
          /* @__PURE__ */ jsx(TrustBadge, { label: "Trusted by merchants" }),
          /* @__PURE__ */ jsx(TrustBadge, { label: "Secure payments" }),
          /* @__PURE__ */ jsx(TrustBadge, { label: "Instant rewards" })
        ] })
      ] }),
      /* @__PURE__ */ jsx("footer", { className: "relative z-10 text-center py-8 text-sm text-gray-400 border-t border-lucky-100", children: /* @__PURE__ */ jsxs("p", { children: [
        "© ",
        (/* @__PURE__ */ new Date()).getFullYear(),
        " Kutoot. Scratch, Win, Repeat!"
      ] }) })
    ] })
  ] });
}
function StepCard({ step, icon, title, description }) {
  return /* @__PURE__ */ jsxs("div", { className: "flex flex-col items-center text-center relative z-10 px-4 py-2", children: [
    /* @__PURE__ */ jsxs("div", { className: "w-24 h-24 rounded-full bg-white border-4 border-dashed border-lucky-300 flex items-center justify-center mb-4 shadow-lg relative", children: [
      /* @__PURE__ */ jsx("span", { className: "text-3xl", children: icon }),
      /* @__PURE__ */ jsx("span", { className: "absolute -top-2 -right-2 w-7 h-7 rounded-full lucky-gradient text-white text-xs font-bold flex items-center justify-center shadow-md", children: step })
    ] }),
    /* @__PURE__ */ jsx("h3", { className: "font-display text-base text-gray-900 mb-1", children: title }),
    /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-500 max-w-[200px]", children: description })
  ] });
}
function FeatureTicket({ emoji, title, description, color }) {
  const styles = {
    lucky: {
      border: "border-lucky-300 hover:border-lucky-400",
      bg: "from-lucky-50 to-lucky-100/50",
      text: "text-lucky-700",
      iconBg: "bg-lucky-100"
    },
    ticket: {
      border: "border-ticket-300 hover:border-ticket-400",
      bg: "from-ticket-50 to-ticket-100/50",
      text: "text-ticket-700",
      iconBg: "bg-ticket-100"
    },
    prize: {
      border: "border-prize-300 hover:border-prize-400",
      bg: "from-prize-50 to-prize-100/50",
      text: "text-prize-700",
      iconBg: "bg-prize-100"
    }
  };
  const s2 = styles[color] || styles.lucky;
  return /* @__PURE__ */ jsxs("div", { className: `coupon-card group hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 hover:scale-[1.02] ${s2.border}`, children: [
    /* @__PURE__ */ jsxs("div", { className: `bg-gradient-to-b ${s2.bg} p-6 sm:p-8 text-center`, children: [
      /* @__PURE__ */ jsx("div", { className: `w-16 h-16 ${s2.iconBg} rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300`, children: /* @__PURE__ */ jsx("span", { className: "text-3xl", children: emoji }) }),
      /* @__PURE__ */ jsx("h3", { className: `font-display text-lg sm:text-xl mb-2 ${s2.text}`, children: title }),
      /* @__PURE__ */ jsx("p", { className: "text-gray-600 text-sm leading-relaxed", children: description })
    ] }),
    /* @__PURE__ */ jsx("div", { className: "flex justify-center gap-2 py-2 bg-gradient-to-r from-transparent via-gray-100 to-transparent", children: [...Array(8)].map((_, i2) => /* @__PURE__ */ jsx("div", { className: "w-2 h-2 rounded-full bg-gray-200" }, i2)) })
  ] });
}
function TrustBadge({ label }) {
  return /* @__PURE__ */ jsxs("div", { className: "flex items-center gap-2 bg-white/80 px-4 py-2 rounded-full border border-lucky-100 shadow-sm", children: [
    /* @__PURE__ */ jsx("span", { className: "text-green-500 text-base", children: "✓" }),
    /* @__PURE__ */ jsx("span", { children: label })
  ] });
}
const __vite_glob_0_18 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: Welcome
}, Symbol.toStringTag, { value: "Module" }));
function t() {
  return t = Object.assign ? Object.assign.bind() : function(t3) {
    for (var e2 = 1; e2 < arguments.length; e2++) {
      var o2 = arguments[e2];
      for (var n2 in o2) ({}).hasOwnProperty.call(o2, n2) && (t3[n2] = o2[n2]);
    }
    return t3;
  }, t.apply(null, arguments);
}
const e = String.prototype.replace, o = /%20/g, n = { RFC1738: function(t3) {
  return e.call(t3, o, "+");
}, RFC3986: function(t3) {
  return String(t3);
} };
var r = "RFC3986";
const i = Object.prototype.hasOwnProperty, s = Array.isArray, u = (function() {
  const t3 = [];
  for (let e2 = 0; e2 < 256; ++e2) t3.push("%" + ((e2 < 16 ? "0" : "") + e2.toString(16)).toUpperCase());
  return t3;
})(), l = function t2(e2, o2, n2) {
  if (!o2) return e2;
  if ("object" != typeof o2) {
    if (s(e2)) e2.push(o2);
    else {
      if (!e2 || "object" != typeof e2) return [e2, o2];
      (n2 && (n2.plainObjects || n2.allowPrototypes) || !i.call(Object.prototype, o2)) && (e2[o2] = true);
    }
    return e2;
  }
  if (!e2 || "object" != typeof e2) return [e2].concat(o2);
  let r2 = e2;
  return s(e2) && !s(o2) && (r2 = (function(t3, e3) {
    const o3 = e3 && e3.plainObjects ? /* @__PURE__ */ Object.create(null) : {};
    for (let e4 = 0; e4 < t3.length; ++e4) void 0 !== t3[e4] && (o3[e4] = t3[e4]);
    return o3;
  })(e2, n2)), s(e2) && s(o2) ? (o2.forEach(function(o3, r3) {
    if (i.call(e2, r3)) {
      const i2 = e2[r3];
      i2 && "object" == typeof i2 && o3 && "object" == typeof o3 ? e2[r3] = t2(i2, o3, n2) : e2.push(o3);
    } else e2[r3] = o3;
  }), e2) : Object.keys(o2).reduce(function(e3, r3) {
    const s2 = o2[r3];
    return e3[r3] = i.call(e3, r3) ? t2(e3[r3], s2, n2) : s2, e3;
  }, r2);
}, c = 1024, a = function(t3, e2) {
  return [].concat(t3, e2);
}, f = function(t3, e2) {
  if (s(t3)) {
    const o2 = [];
    for (let n2 = 0; n2 < t3.length; n2 += 1) o2.push(e2(t3[n2]));
    return o2;
  }
  return e2(t3);
}, p = Object.prototype.hasOwnProperty, y = { brackets: function(t3) {
  return t3 + "[]";
}, comma: "comma", indices: function(t3, e2) {
  return t3 + "[" + e2 + "]";
}, repeat: function(t3) {
  return t3;
} }, d = Array.isArray, h = Array.prototype.push, b = function(t3, e2) {
  h.apply(t3, d(e2) ? e2 : [e2]);
}, m = Date.prototype.toISOString, g = { addQueryPrefix: false, allowDots: false, allowEmptyArrays: false, arrayFormat: "indices", charset: "utf-8", charsetSentinel: false, delimiter: "&", encode: true, encodeDotInKeys: false, encoder: function(t3, e2, o2, n2, r2) {
  if (0 === t3.length) return t3;
  let i2 = t3;
  if ("symbol" == typeof t3 ? i2 = Symbol.prototype.toString.call(t3) : "string" != typeof t3 && (i2 = String(t3)), "iso-8859-1" === o2) return escape(i2).replace(/%u[0-9a-f]{4}/gi, function(t4) {
    return "%26%23" + parseInt(t4.slice(2), 16) + "%3B";
  });
  let s2 = "";
  for (let t4 = 0; t4 < i2.length; t4 += c) {
    const e3 = i2.length >= c ? i2.slice(t4, t4 + c) : i2, o3 = [];
    for (let t5 = 0; t5 < e3.length; ++t5) {
      let n3 = e3.charCodeAt(t5);
      45 === n3 || 46 === n3 || 95 === n3 || 126 === n3 || n3 >= 48 && n3 <= 57 || n3 >= 65 && n3 <= 90 || n3 >= 97 && n3 <= 122 || "RFC1738" === r2 && (40 === n3 || 41 === n3) ? o3[o3.length] = e3.charAt(t5) : n3 < 128 ? o3[o3.length] = u[n3] : n3 < 2048 ? o3[o3.length] = u[192 | n3 >> 6] + u[128 | 63 & n3] : n3 < 55296 || n3 >= 57344 ? o3[o3.length] = u[224 | n3 >> 12] + u[128 | n3 >> 6 & 63] + u[128 | 63 & n3] : (t5 += 1, n3 = 65536 + ((1023 & n3) << 10 | 1023 & e3.charCodeAt(t5)), o3[o3.length] = u[240 | n3 >> 18] + u[128 | n3 >> 12 & 63] + u[128 | n3 >> 6 & 63] + u[128 | 63 & n3]);
    }
    s2 += o3.join("");
  }
  return s2;
}, encodeValuesOnly: false, format: r, formatter: n[r], indices: false, serializeDate: function(t3) {
  return m.call(t3);
}, skipNulls: false, strictNullHandling: false }, w = {}, v = function(t3, e2, o2, n2, r2, i2, s2, u2, l2, c2, a2, p2, y2, h2, m2, j2, $2, E2) {
  let O2 = t3, T2 = E2, R2 = 0, S2 = false;
  for (; void 0 !== (T2 = T2.get(w)) && !S2; ) {
    const e3 = T2.get(t3);
    if (R2 += 1, void 0 !== e3) {
      if (e3 === R2) throw new RangeError("Cyclic object value");
      S2 = true;
    }
    void 0 === T2.get(w) && (R2 = 0);
  }
  if ("function" == typeof c2 ? O2 = c2(e2, O2) : O2 instanceof Date ? O2 = y2(O2) : "comma" === o2 && d(O2) && (O2 = f(O2, function(t4) {
    return t4 instanceof Date ? y2(t4) : t4;
  })), null === O2) {
    if (i2) return l2 && !j2 ? l2(e2, g.encoder, $2, "key", h2) : e2;
    O2 = "";
  }
  if ("string" == typeof (I2 = O2) || "number" == typeof I2 || "boolean" == typeof I2 || "symbol" == typeof I2 || "bigint" == typeof I2 || (function(t4) {
    return !(!t4 || "object" != typeof t4 || !(t4.constructor && t4.constructor.isBuffer && t4.constructor.isBuffer(t4)));
  })(O2)) return l2 ? [m2(j2 ? e2 : l2(e2, g.encoder, $2, "key", h2)) + "=" + m2(l2(O2, g.encoder, $2, "value", h2))] : [m2(e2) + "=" + m2(String(O2))];
  var I2;
  const A2 = [];
  if (void 0 === O2) return A2;
  let D2;
  if ("comma" === o2 && d(O2)) j2 && l2 && (O2 = f(O2, l2)), D2 = [{ value: O2.length > 0 ? O2.join(",") || null : void 0 }];
  else if (d(c2)) D2 = c2;
  else {
    const t4 = Object.keys(O2);
    D2 = a2 ? t4.sort(a2) : t4;
  }
  const _ = u2 ? e2.replace(/\./g, "%2E") : e2, k = n2 && d(O2) && 1 === O2.length ? _ + "[]" : _;
  if (r2 && d(O2) && 0 === O2.length) return k + "[]";
  for (let e3 = 0; e3 < D2.length; ++e3) {
    const f2 = D2[e3], g2 = "object" == typeof f2 && void 0 !== f2.value ? f2.value : O2[f2];
    if (s2 && null === g2) continue;
    const T3 = p2 && u2 ? f2.replace(/\./g, "%2E") : f2, S3 = d(O2) ? "function" == typeof o2 ? o2(k, T3) : k : k + (p2 ? "." + T3 : "[" + T3 + "]");
    E2.set(t3, R2);
    const I3 = /* @__PURE__ */ new WeakMap();
    I3.set(w, E2), b(A2, v(g2, S3, o2, n2, r2, i2, s2, u2, "comma" === o2 && j2 && d(O2) ? null : l2, c2, a2, p2, y2, h2, m2, j2, $2, I3));
  }
  return A2;
}, j = Object.prototype.hasOwnProperty, $ = Array.isArray, E = { allowDots: false, allowEmptyArrays: false, allowPrototypes: false, allowSparse: false, arrayLimit: 20, charset: "utf-8", charsetSentinel: false, comma: false, decodeDotInKeys: false, decoder: function(t3, e2, o2) {
  const n2 = t3.replace(/\+/g, " ");
  if ("iso-8859-1" === o2) return n2.replace(/%[0-9a-f]{2}/gi, unescape);
  try {
    return decodeURIComponent(n2);
  } catch (t4) {
    return n2;
  }
}, delimiter: "&", depth: 5, duplicates: "combine", ignoreQueryPrefix: false, interpretNumericEntities: false, parameterLimit: 1e3, parseArrays: true, plainObjects: false, strictNullHandling: false }, O = function(t3) {
  return t3.replace(/&#(\d+);/g, function(t4, e2) {
    return String.fromCharCode(parseInt(e2, 10));
  });
}, T = function(t3, e2) {
  return t3 && "string" == typeof t3 && e2.comma && t3.indexOf(",") > -1 ? t3.split(",") : t3;
}, R = function(t3, e2, o2, n2) {
  if (!t3) return;
  const r2 = o2.allowDots ? t3.replace(/\.([^.[]+)/g, "[$1]") : t3, i2 = /(\[[^[\]]*])/g;
  let s2 = o2.depth > 0 && /(\[[^[\]]*])/.exec(r2);
  const u2 = s2 ? r2.slice(0, s2.index) : r2, l2 = [];
  if (u2) {
    if (!o2.plainObjects && j.call(Object.prototype, u2) && !o2.allowPrototypes) return;
    l2.push(u2);
  }
  let c2 = 0;
  for (; o2.depth > 0 && null !== (s2 = i2.exec(r2)) && c2 < o2.depth; ) {
    if (c2 += 1, !o2.plainObjects && j.call(Object.prototype, s2[1].slice(1, -1)) && !o2.allowPrototypes) return;
    l2.push(s2[1]);
  }
  return s2 && l2.push("[" + r2.slice(s2.index) + "]"), (function(t4, e3, o3, n3) {
    let r3 = n3 ? e3 : T(e3, o3);
    for (let e4 = t4.length - 1; e4 >= 0; --e4) {
      let n4;
      const i3 = t4[e4];
      if ("[]" === i3 && o3.parseArrays) n4 = o3.allowEmptyArrays && "" === r3 ? [] : [].concat(r3);
      else {
        n4 = o3.plainObjects ? /* @__PURE__ */ Object.create(null) : {};
        const t5 = "[" === i3.charAt(0) && "]" === i3.charAt(i3.length - 1) ? i3.slice(1, -1) : i3, e5 = o3.decodeDotInKeys ? t5.replace(/%2E/g, ".") : t5, s3 = parseInt(e5, 10);
        o3.parseArrays || "" !== e5 ? !isNaN(s3) && i3 !== e5 && String(s3) === e5 && s3 >= 0 && o3.parseArrays && s3 <= o3.arrayLimit ? (n4 = [], n4[s3] = r3) : "__proto__" !== e5 && (n4[e5] = r3) : n4 = { 0: r3 };
      }
      r3 = n4;
    }
    return r3;
  })(l2, e2, o2, n2);
};
function S(t3, e2) {
  const o2 = /* @__PURE__ */ (function(t4) {
    return E;
  })();
  if ("" === t3 || null == t3) return o2.plainObjects ? /* @__PURE__ */ Object.create(null) : {};
  const n2 = "string" == typeof t3 ? (function(t4, e3) {
    const o3 = { __proto__: null }, n3 = (e3.ignoreQueryPrefix ? t4.replace(/^\?/, "") : t4).split(e3.delimiter, Infinity === e3.parameterLimit ? void 0 : e3.parameterLimit);
    let r3, i3 = -1, s2 = e3.charset;
    if (e3.charsetSentinel) for (r3 = 0; r3 < n3.length; ++r3) 0 === n3[r3].indexOf("utf8=") && ("utf8=%E2%9C%93" === n3[r3] ? s2 = "utf-8" : "utf8=%26%2310003%3B" === n3[r3] && (s2 = "iso-8859-1"), i3 = r3, r3 = n3.length);
    for (r3 = 0; r3 < n3.length; ++r3) {
      if (r3 === i3) continue;
      const t5 = n3[r3], u2 = t5.indexOf("]="), l2 = -1 === u2 ? t5.indexOf("=") : u2 + 1;
      let c2, p2;
      -1 === l2 ? (c2 = e3.decoder(t5, E.decoder, s2, "key"), p2 = e3.strictNullHandling ? null : "") : (c2 = e3.decoder(t5.slice(0, l2), E.decoder, s2, "key"), p2 = f(T(t5.slice(l2 + 1), e3), function(t6) {
        return e3.decoder(t6, E.decoder, s2, "value");
      })), p2 && e3.interpretNumericEntities && "iso-8859-1" === s2 && (p2 = O(p2)), t5.indexOf("[]=") > -1 && (p2 = $(p2) ? [p2] : p2);
      const y2 = j.call(o3, c2);
      y2 && "combine" === e3.duplicates ? o3[c2] = a(o3[c2], p2) : y2 && "last" !== e3.duplicates || (o3[c2] = p2);
    }
    return o3;
  })(t3, o2) : t3;
  let r2 = o2.plainObjects ? /* @__PURE__ */ Object.create(null) : {};
  const i2 = Object.keys(n2);
  for (let e3 = 0; e3 < i2.length; ++e3) {
    const s2 = i2[e3], u2 = R(s2, n2[s2], o2, "string" == typeof t3);
    r2 = l(r2, u2, o2);
  }
  return true === o2.allowSparse ? r2 : (function(t4) {
    const e3 = [{ obj: { o: t4 }, prop: "o" }], o3 = [];
    for (let t5 = 0; t5 < e3.length; ++t5) {
      const n3 = e3[t5], r3 = n3.obj[n3.prop], i3 = Object.keys(r3);
      for (let t6 = 0; t6 < i3.length; ++t6) {
        const n4 = i3[t6], s2 = r3[n4];
        "object" == typeof s2 && null !== s2 && -1 === o3.indexOf(s2) && (e3.push({ obj: r3, prop: n4 }), o3.push(s2));
      }
    }
    return (function(t5) {
      for (; t5.length > 1; ) {
        const e4 = t5.pop(), o4 = e4.obj[e4.prop];
        if (s(o4)) {
          const t6 = [];
          for (let e5 = 0; e5 < o4.length; ++e5) void 0 !== o4[e5] && t6.push(o4[e5]);
          e4.obj[e4.prop] = t6;
        }
      }
    })(e3), t4;
  })(r2);
}
class I {
  constructor(t3, e2, o2) {
    var n2, r2;
    this.name = t3, this.definition = e2, this.bindings = null != (n2 = e2.bindings) ? n2 : {}, this.wheres = null != (r2 = e2.wheres) ? r2 : {}, this.config = o2;
  }
  get template() {
    const t3 = `${this.origin}/${this.definition.uri}`.replace(/\/+$/, "");
    return "" === t3 ? "/" : t3;
  }
  get origin() {
    return this.config.absolute ? this.definition.domain ? `${this.config.url.match(/^\w+:\/\//)[0]}${this.definition.domain}${this.config.port ? `:${this.config.port}` : ""}` : this.config.url : "";
  }
  get parameterSegments() {
    var t3, e2;
    return null != (t3 = null == (e2 = this.template.match(/{[^}?]+\??}/g)) ? void 0 : e2.map((t4) => ({ name: t4.replace(/{|\??}/g, ""), required: !/\?}$/.test(t4) }))) ? t3 : [];
  }
  matchesUrl(t3) {
    var e2;
    if (!this.definition.methods.includes("GET")) return false;
    const o2 = this.template.replace(/[.*+$()[\]]/g, "\\$&").replace(/(\/?){([^}?]*)(\??)}/g, (t4, e3, o3, n3) => {
      var r3;
      const i3 = `(?<${o3}>${(null == (r3 = this.wheres[o3]) ? void 0 : r3.replace(/(^\^)|(\$$)/g, "")) || "[^/?]+"})`;
      return n3 ? `(${e3}${i3})?` : `${e3}${i3}`;
    }).replace(/^\w+:\/\//, ""), [n2, r2] = t3.replace(/^\w+:\/\//, "").split("?"), i2 = null != (e2 = new RegExp(`^${o2}/?$`).exec(n2)) ? e2 : new RegExp(`^${o2}/?$`).exec(decodeURI(n2));
    if (i2) {
      for (const t4 in i2.groups) i2.groups[t4] = "string" == typeof i2.groups[t4] ? decodeURIComponent(i2.groups[t4]) : i2.groups[t4];
      return { params: i2.groups, query: S(r2) };
    }
    return false;
  }
  compile(t3) {
    return this.parameterSegments.length ? this.template.replace(/{([^}?]+)(\??)}/g, (e2, o2, n2) => {
      var r2, i2;
      if (!n2 && [null, void 0].includes(t3[o2])) throw new Error(`Ziggy error: '${o2}' parameter is required for route '${this.name}'.`);
      if (this.wheres[o2] && !new RegExp(`^${n2 ? `(${this.wheres[o2]})?` : this.wheres[o2]}$`).test(null != (i2 = t3[o2]) ? i2 : "")) throw new Error(`Ziggy error: '${o2}' parameter '${t3[o2]}' does not match required format '${this.wheres[o2]}' for route '${this.name}'.`);
      return encodeURI(null != (r2 = t3[o2]) ? r2 : "").replace(/%7C/g, "|").replace(/%25/g, "%").replace(/\$/g, "%24");
    }).replace(this.config.absolute ? /(\.[^/]+?)(\/\/)/ : /(^)(\/\/)/, "$1/").replace(/\/+$/, "") : this.template;
  }
}
class A extends String {
  constructor(e2, o2, n2 = true, r2) {
    if (super(), this.t = null != r2 ? r2 : "undefined" != typeof Ziggy ? Ziggy : null == globalThis ? void 0 : globalThis.Ziggy, !this.t && "undefined" != typeof document && document.getElementById("ziggy-routes-json") && (globalThis.Ziggy = JSON.parse(document.getElementById("ziggy-routes-json").textContent), this.t = globalThis.Ziggy), this.t = t({}, this.t, { absolute: n2 }), e2) {
      if (!this.t.routes[e2]) throw new Error(`Ziggy error: route '${e2}' is not in the route list.`);
      this.i = new I(e2, this.t.routes[e2], this.t), this.u = this.l(o2);
    }
  }
  toString() {
    const e2 = Object.keys(this.u).filter((t3) => !this.i.parameterSegments.some(({ name: e3 }) => e3 === t3)).filter((t3) => "_query" !== t3).reduce((e3, o2) => t({}, e3, { [o2]: this.u[o2] }), {});
    return this.i.compile(this.u) + (function(t3, e3) {
      let o2 = t3;
      const i2 = (function(t4) {
        if (!t4) return g;
        if (void 0 !== t4.allowEmptyArrays && "boolean" != typeof t4.allowEmptyArrays) throw new TypeError("`allowEmptyArrays` option can only be `true` or `false`, when provided");
        if (void 0 !== t4.encodeDotInKeys && "boolean" != typeof t4.encodeDotInKeys) throw new TypeError("`encodeDotInKeys` option can only be `true` or `false`, when provided");
        if (null != t4.encoder && "function" != typeof t4.encoder) throw new TypeError("Encoder has to be a function.");
        const e4 = t4.charset || g.charset;
        if (void 0 !== t4.charset && "utf-8" !== t4.charset && "iso-8859-1" !== t4.charset) throw new TypeError("The charset option must be either utf-8, iso-8859-1, or undefined");
        let o3 = r;
        if (void 0 !== t4.format) {
          if (!p.call(n, t4.format)) throw new TypeError("Unknown format option provided.");
          o3 = t4.format;
        }
        const i3 = n[o3];
        let s3, u3 = g.filter;
        if (("function" == typeof t4.filter || d(t4.filter)) && (u3 = t4.filter), s3 = t4.arrayFormat in y ? t4.arrayFormat : "indices" in t4 ? t4.indices ? "indices" : "repeat" : g.arrayFormat, "commaRoundTrip" in t4 && "boolean" != typeof t4.commaRoundTrip) throw new TypeError("`commaRoundTrip` must be a boolean, or absent");
        return { addQueryPrefix: "boolean" == typeof t4.addQueryPrefix ? t4.addQueryPrefix : g.addQueryPrefix, allowDots: void 0 === t4.allowDots ? true === t4.encodeDotInKeys || g.allowDots : !!t4.allowDots, allowEmptyArrays: "boolean" == typeof t4.allowEmptyArrays ? !!t4.allowEmptyArrays : g.allowEmptyArrays, arrayFormat: s3, charset: e4, charsetSentinel: "boolean" == typeof t4.charsetSentinel ? t4.charsetSentinel : g.charsetSentinel, commaRoundTrip: t4.commaRoundTrip, delimiter: void 0 === t4.delimiter ? g.delimiter : t4.delimiter, encode: "boolean" == typeof t4.encode ? t4.encode : g.encode, encodeDotInKeys: "boolean" == typeof t4.encodeDotInKeys ? t4.encodeDotInKeys : g.encodeDotInKeys, encoder: "function" == typeof t4.encoder ? t4.encoder : g.encoder, encodeValuesOnly: "boolean" == typeof t4.encodeValuesOnly ? t4.encodeValuesOnly : g.encodeValuesOnly, filter: u3, format: o3, formatter: i3, serializeDate: "function" == typeof t4.serializeDate ? t4.serializeDate : g.serializeDate, skipNulls: "boolean" == typeof t4.skipNulls ? t4.skipNulls : g.skipNulls, sort: "function" == typeof t4.sort ? t4.sort : null, strictNullHandling: "boolean" == typeof t4.strictNullHandling ? t4.strictNullHandling : g.strictNullHandling };
      })(e3);
      let s2, u2;
      "function" == typeof i2.filter ? (u2 = i2.filter, o2 = u2("", o2)) : d(i2.filter) && (u2 = i2.filter, s2 = u2);
      const l2 = [];
      if ("object" != typeof o2 || null === o2) return "";
      const c2 = y[i2.arrayFormat], a2 = "comma" === c2 && i2.commaRoundTrip;
      s2 || (s2 = Object.keys(o2)), i2.sort && s2.sort(i2.sort);
      const f2 = /* @__PURE__ */ new WeakMap();
      for (let t4 = 0; t4 < s2.length; ++t4) {
        const e4 = s2[t4];
        i2.skipNulls && null === o2[e4] || b(l2, v(o2[e4], e4, c2, a2, i2.allowEmptyArrays, i2.strictNullHandling, i2.skipNulls, i2.encodeDotInKeys, i2.encode ? i2.encoder : null, i2.filter, i2.sort, i2.allowDots, i2.serializeDate, i2.format, i2.formatter, i2.encodeValuesOnly, i2.charset, f2));
      }
      const h2 = l2.join(i2.delimiter);
      let m2 = true === i2.addQueryPrefix ? "?" : "";
      return i2.charsetSentinel && (m2 += "iso-8859-1" === i2.charset ? "utf8=%26%2310003%3B&" : "utf8=%E2%9C%93&"), h2.length > 0 ? m2 + h2 : "";
    })(t({}, e2, this.u._query), { addQueryPrefix: true, arrayFormat: "indices", encodeValuesOnly: true, skipNulls: true, encoder: (t3, e3) => "boolean" == typeof t3 ? Number(t3) : e3(t3) });
  }
  p(e2) {
    e2 ? this.t.absolute && e2.startsWith("/") && (e2 = this.h().host + e2) : e2 = this.m();
    let o2 = {};
    const [n2, r2] = Object.entries(this.t.routes).find(([t3, n3]) => o2 = new I(t3, n3, this.t).matchesUrl(e2)) || [void 0, void 0];
    return t({ name: n2 }, o2, { route: r2 });
  }
  m() {
    const { host: t3, pathname: e2, search: o2 } = this.h();
    return (this.t.absolute ? t3 + e2 : e2.replace(this.t.url.replace(/^\w*:\/\/[^/]+/, ""), "").replace(/^\/+/, "/")) + o2;
  }
  current(e2, o2) {
    const { name: n2, params: r2, query: i2, route: s2 } = this.p();
    if (!e2) return n2;
    const u2 = new RegExp(`^${e2.replace(/\./g, "\\.").replace(/\*/g, ".*")}$`).test(n2);
    if ([null, void 0].includes(o2) || !u2) return u2;
    const l2 = new I(n2, s2, this.t);
    o2 = this.l(o2, l2);
    const c2 = t({}, r2, i2);
    if (Object.values(o2).every((t3) => !t3) && !Object.values(c2).some((t3) => void 0 !== t3)) return true;
    const a2 = (t3, e3) => Object.entries(t3).every(([t4, o3]) => Array.isArray(o3) && Array.isArray(e3[t4]) ? o3.every((o4) => e3[t4].includes(o4) || e3[t4].includes(decodeURIComponent(o4))) : "object" == typeof o3 && "object" == typeof e3[t4] && null !== o3 && null !== e3[t4] ? a2(o3, e3[t4]) : e3[t4] == o3 || e3[t4] == decodeURIComponent(o3));
    return a2(o2, c2);
  }
  h() {
    var t3, e2, o2, n2, r2, i2;
    const { host: s2 = "", pathname: u2 = "", search: l2 = "" } = "undefined" != typeof window ? window.location : {};
    return { host: null != (t3 = null == (e2 = this.t.location) ? void 0 : e2.host) ? t3 : s2, pathname: null != (o2 = null == (n2 = this.t.location) ? void 0 : n2.pathname) ? o2 : u2, search: null != (r2 = null == (i2 = this.t.location) ? void 0 : i2.search) ? r2 : l2 };
  }
  get params() {
    const { params: e2, query: o2 } = this.p();
    return t({}, e2, o2);
  }
  get routeParams() {
    return this.p().params;
  }
  get queryParams() {
    return this.p().query;
  }
  has(t3) {
    return this.t.routes.hasOwnProperty(t3);
  }
  l(e2 = {}, o2 = this.i) {
    null != e2 || (e2 = {}), e2 = ["string", "number"].includes(typeof e2) ? [e2] : e2;
    const n2 = o2.parameterSegments.filter(({ name: t3 }) => !this.t.defaults[t3]);
    return Array.isArray(e2) ? e2 = e2.reduce((e3, o3, r2) => t({}, e3, n2[r2] ? { [n2[r2].name]: o3 } : "object" == typeof o3 ? o3 : { [o3]: "" }), {}) : 1 !== n2.length || e2[n2[0].name] || !e2.hasOwnProperty(Object.values(o2.bindings)[0]) && !e2.hasOwnProperty("id") || (e2 = { [n2[0].name]: e2 }), t({}, this.v(o2), this.j(e2, o2));
  }
  v(e2) {
    return e2.parameterSegments.filter(({ name: t3 }) => this.t.defaults[t3]).reduce((e3, { name: o2 }, n2) => t({}, e3, { [o2]: this.t.defaults[o2] }), {});
  }
  j(e2, { bindings: o2, parameterSegments: n2 }) {
    return Object.entries(e2).reduce((e3, [r2, i2]) => {
      if (!i2 || "object" != typeof i2 || Array.isArray(i2) || !n2.some(({ name: t3 }) => t3 === r2)) return t({}, e3, { [r2]: i2 });
      if (!i2.hasOwnProperty(o2[r2])) {
        if (!i2.hasOwnProperty("id")) throw new Error(`Ziggy error: object passed as '${r2}' parameter is missing route model binding key '${o2[r2]}'.`);
        o2[r2] = "id";
      }
      return t({}, e3, { [r2]: i2[o2[r2]] });
    }, {});
  }
  valueOf() {
    return this.toString();
  }
}
function D(t3, e2, o2, n2) {
  const r2 = new A(t3, e2, o2, n2);
  return t3 ? r2.toString() : r2;
}
createServer(
  (page) => createInertiaApp({
    page,
    render: ReactDOMServer.renderToString,
    title: (title) => `${title} - Kutoot`,
    resolve: (name) => {
      const pages = /* @__PURE__ */ Object.assign({ "./Pages/Auth/ConfirmPassword.jsx": __vite_glob_0_0, "./Pages/Auth/ForgotPassword.jsx": __vite_glob_0_1, "./Pages/Auth/Login.jsx": __vite_glob_0_2, "./Pages/Auth/OtpLogin.jsx": __vite_glob_0_3, "./Pages/Auth/Register.jsx": __vite_glob_0_4, "./Pages/Auth/ResetPassword.jsx": __vite_glob_0_5, "./Pages/Auth/VerifyEmail.jsx": __vite_glob_0_6, "./Pages/Campaigns/Index.jsx": __vite_glob_0_7, "./Pages/Campaigns/Show.jsx": __vite_glob_0_8, "./Pages/Coupons/Index.jsx": __vite_glob_0_9, "./Pages/Dashboard.jsx": __vite_glob_0_10, "./Pages/Executive/LinkQr.jsx": __vite_glob_0_11, "./Pages/Profile/Edit.jsx": __vite_glob_0_12, "./Pages/Profile/Partials/DeleteUserForm.jsx": __vite_glob_0_13, "./Pages/Profile/Partials/UpdatePasswordForm.jsx": __vite_glob_0_14, "./Pages/Profile/Partials/UpdateProfileInformationForm.jsx": __vite_glob_0_15, "./Pages/Subscriptions/Index.jsx": __vite_glob_0_16, "./Pages/Transactions/Index.jsx": __vite_glob_0_17, "./Pages/Welcome.jsx": __vite_glob_0_18 });
      return pages[`./Pages/${name}.jsx`];
    },
    setup: ({ App: App2, props }) => {
      global.route = (name, params, absolute) => D(name, params, absolute, {
        ...page.props.ziggy,
        location: new URL(page.props.ziggy.location)
      });
      return /* @__PURE__ */ jsx(App2, { ...props });
    }
  })
);
