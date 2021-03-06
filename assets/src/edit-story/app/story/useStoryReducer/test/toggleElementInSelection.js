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

describe('toggleElementInSelection', () => {
  it('should add/remove element in selection', () => {
    const { restore, toggleElementInSelection } = setupReducer();

    // Set an initial state.
    const initialState = restore({
      pages: [
        { id: '111', elements: [{ id: 'e1' }, { id: 'e2' }, { id: 'e3' }] },
      ],
      current: '111',
      selection: ['e1', 'e2'],
    });

    expect(initialState.selection).toContain('e1');

    // Toggle element e1 - which would remove it from selection
    const firstResult = toggleElementInSelection({ elementId: 'e1' });
    expect(firstResult.selection).not.toContain('e1');

    // Toggle element e1 again - which would add it to selection
    const secondResult = toggleElementInSelection({ elementId: 'e1' });
    expect(secondResult.selection).toContain('e1');
  });

  it('should ignore missing element id', () => {
    const { restore, toggleElementInSelection } = setupReducer();

    // Set an initial state.
    const initialState = restore({
      pages: [
        { id: '111', elements: [{ id: 'e1' }, { id: 'e2' }, { id: 'e3' }] },
      ],
      current: '111',
      selection: ['e1', 'e2'],
    });

    // Toggle no element
    const failedAttempt = toggleElementInSelection({ elementId: null });
    expect(failedAttempt).toStrictEqual(initialState);
  });

  it('should not allow adding background element to non-empty selection', () => {
    const { restore, toggleElementInSelection } = setupReducer();

    // Set an initial state.
    const initialState = restore({
      pages: [
        {
          id: '111',
          backgroundElementId: 'e1',
          elements: [{ id: 'e1' }, { id: 'e2' }, { id: 'e3' }],
        },
      ],
      current: '111',
      selection: ['e2', 'e3'],
    });

    // Toggle no element
    const failedAttempt = toggleElementInSelection({ elementId: 'e1' });
    expect(failedAttempt).toStrictEqual(initialState);
  });
});
