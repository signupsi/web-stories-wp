/*
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe('restore', () => {
  it('should restore pages while defaulting selection and current', () => {
    const reducer = setupReducer();

    const { restore } = reducer;

    // Restore only pages variable, leave the other as defaults.
    const pages = [{ id: '123' }];
    const result = restore({ pages });

    expect(result).toStrictEqual({
      pages,
      selection: [],
      current: '123',
      story: {},
      capabilities: {},
    });
  });

  it('should restore pages, current and selection', () => {
    const reducer = setupReducer();

    const { restore } = reducer;

    const result = restore({
      pages: [{ id: '111' }, { id: '222', elements: [{ id: '333' }] }],
      current: '222',
      selection: ['333'],
    });

    expect(result.pages).toHaveLength(2);
    expect(result.current).toBe('222');
    expect(result.selection).toHaveLength(1);
  });

  it('should restore selection to a unique set', () => {
    const reducer = setupReducer();

    const { restore } = reducer;

    const result = restore({
      pages: [{ id: '111', elements: [{ id: '222' }] }],
      current: '111',
      selection: ['222', '222'],
    });

    expect(result.selection).toHaveLength(1);
  });

  it('should restore story if set', () => {
    const reducer = setupReducer();

    const { restore } = reducer;

    const result = restore({
      pages: [{ id: '111' }],
      story: { a: 1, b: [2] },
    });

    expect(result.story).toStrictEqual({ a: 1, b: [2] });
  });

  it('should force current to be a valid page id', () => {
    const reducer = setupReducer();

    const { restore } = reducer;

    const result = restore({
      pages: [{ id: '111' }],
      current: '222',
    });

    expect(result.current).toStrictEqual('111');
  });

  it('should restore to an empty state if no pages', () => {
    const reducer = setupReducer();

    const { restore } = reducer;

    const result = restore({
      pages: [],
      current: '111',
      selection: ['222'],
      story: { a: 1 },
      capabilities: { b: 2 },
    });

    expect(result).toStrictEqual({
      pages: [],
      selection: [],
      current: null,
      story: {},
      capabilities: {},
    });
  });

  it('should restore to an empty state if empty', () => {
    const reducer = setupReducer();

    const { restore } = reducer;

    const result = restore({});

    expect(result).toStrictEqual({
      pages: [],
      selection: [],
      current: null,
      story: {},
      capabilities: {},
    });
  });

  it('should override existing content', () => {
    const reducer = setupReducer();

    const { restore } = reducer;

    // First restore to some non-initial state.
    const stateWithContent = restore({
      pages: [{ id: '111' }, { id: '222', elements: [{ id: '333' }] }],
      current: '222',
      selection: ['333'],
      story: { a: 1 },
      capabilities: { b: 2 },
    });

    // And validate that it is non-initial.
    expect(stateWithContent.pages).not.toHaveLength(0);
    expect(stateWithContent.current).not.toBeNull();
    expect(stateWithContent.selection).not.toHaveLength(0);
    expect(Object.keys(stateWithContent.story)).not.toHaveLength(0);
    expect(Object.keys(stateWithContent.capabilities)).not.toHaveLength(0);

    // Then override by restoring to a new state.
    const pages = [{ id: '123' }];
    const result = restore({ pages });

    expect(result).toStrictEqual({
      pages,
      selection: [],
      current: '123',
      story: {},
      capabilities: {},
    });
  });
});
