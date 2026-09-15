import { createSlice, PayloadAction } from "@reduxjs/toolkit";

interface PostFilterState {
  search: string;

  status: string;

  page: number;
}

const initialState: PostFilterState = {
  search: "",

  status: "",

  page: 1,
};

const slice = createSlice({
  name: "postFilters",

  initialState,

  reducers: {
    setSearch(state, action: PayloadAction<string>) {
      state.search = action.payload;

      state.page = 1;
    },

    setStatus(state, action: PayloadAction<string>) {
      state.status = action.payload;

      state.page = 1;
    },

    setPage(state, action: PayloadAction<number>) {
      state.page = action.payload;
    },

    resetFilters() {
      return initialState;
    },
  },
});

export const {
  setSearch,
  setStatus,
  setPage,
  resetFilters,
} = slice.actions;

export default slice.reducer;