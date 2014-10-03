package com.hoheart.hfc;

import android.view.View;
import android.view.ViewGroup;

public interface Adapter {

	public int getViewType(int position);

	public View getView(int position, View convertView, ViewGroup parent);

	public int getCurrentPosition();

	public boolean isLast(int position);

	public boolean isAfterLast(int position);

	public boolean isFirst(int position);

	public boolean isBeforeFirst(int position);
}
